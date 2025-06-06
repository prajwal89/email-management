<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\IdentificationHeader;

/**
 * Handle all CRUD options for the email headers
 */
class HeadersManager
{
    public function __construct(public Email $email) {}

    /**
     * Initial set headers before message sending
     */
    public function configureEmailHeaders(
        Model $eventable,
        EmailReceivable $receivable,
        ?array $eventContext
    ) {
        $this->createMessageId();
        $this->addUnsubscribeHeader();

        $this->email->returnPath(config('email-management.return_path'));

        $this->email->replyTo(config('email-management.reply_to'));

        $headers = $this->email->getHeaders();
        $headers->addTextHeader('X-Eventable-Type', (string) get_class($eventable));
        $headers->addTextHeader('X-Eventable-Id', (string) $eventable->getKey());
        $headers->addTextHeader('X-Receivable-Type', (string) get_class($receivable));
        $headers->addTextHeader('X-Receivable-Id', (string) $receivable->getKey());

        if ($eventContext !== null) {
            $headers->addTextHeader('X-Event-Context', json_encode($eventContext));
        }
    }

    public function createOrGetMessageId(): string
    {
        $messageId = $this->getMessageId();

        if ($messageId === null) {
            $messageId = $this->createMessageId();
        }

        return $messageId;
    }

    public function createMessageId(): string
    {
        $messageId = uniqid() . '@' . (config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'localhost');

        $this->email->getHeaders()->addIdHeader('Message-ID', $messageId);

        return $messageId;
    }

    public function getMessageId(): ?string
    {
        $headers = $this->email->getHeaders();

        $messageId = null;

        // Correct way to get Message-ID
        if ($headers->has('Message-ID')) {
            $messageIdHeader = $headers->get('Message-ID');
            if ($messageIdHeader instanceof IdentificationHeader) {
                $messageId = $messageIdHeader->getId();
            }
        }

        return $messageId;
    }

    public function getEventContext(): ?array
    {
        return $this->email->getHeaders()->has('X-Event-Context') ?
            json_decode($this->email->getHeaders()->getHeaderBody('X-Event-Context'), true)
            : null;
    }

    public function getEventable(): array
    {
        $eventableType = $this->email->getHeaders()->has('X-Eventable-Type')
            ? $this->email->getHeaders()->getHeaderBody('X-Eventable-Type')
            : null;

        $eventableId = $this->email->getHeaders()->has('X-Eventable-Id')
            ? $this->email->getHeaders()->getHeaderBody('X-Eventable-Id')
            : null;

        return [
            'type' => $eventableType,
            'id' => $eventableId,
        ];
    }

    public function getReceivable(): array
    {
        $receivableType = $this->email->getHeaders()->has('X-Receivable-Type')
            ? $this->email->getHeaders()->getHeaderBody('X-Receivable-Type')
            : null;

        $receivableId = $this->email->getHeaders()->has('X-Receivable-Id')
            ? $this->email->getHeaders()->getHeaderBody('X-Receivable-Id')
            : null;

        return [
            'type' => $receivableType,
            'id' => $receivableId,
        ];
    }

    public function removeHeaders(): void
    {
        $headers = $this->email->getHeaders();

        $headers->remove('X-Eventable-Type');
        $headers->remove('X-Eventable-Id');
        $headers->remove('X-Receivable-Type');
        $headers->remove('X-Receivable-Id');
        $headers->remove('X-Event-Context');
    }

    // public  function attachMailerHashHeader(): string
    // {
    //     // handles normal emails that are not sent from email hadler
    //     // is this required ?
    //     $hash = str()->random(32);
    //     $headers->addTextHeader('X-Mailer-Hash', (string) $hash);

    //     return $hash;
    // }

    public function addUnsubscribeHeader(): void
    {
        $unsubscribeUrl = URL::signedRoute('emails.unsubscribe', [
            'hash' => 'test',
        ]);

        $this->email->getHeaders()->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');

        $this->email->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
    }
}
