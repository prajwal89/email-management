<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Support\Facades\URL;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailVariant;
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
        EmailSendable $sendable,
        EmailReceivable $receivable,
        EmailVariant $chosenEmailVariant,
        ?string $messageId,
        ?array $eventContext
    ) {
        $this->createMessageId($messageId);

        $this->addUnsubscribeHeader($messageId);

        $returnPath = config('email-management.return_path');

        if ($returnPath) {
            $this->email->returnPath($returnPath);
        }

        $replyTo = config('email-management.reply_to');

        if ($replyTo) {
            $this->email->replyTo($replyTo);
        }

        $headers = $this->email->getHeaders();

        $headers->addTextHeader('X-Sendable-Type', (string) get_class($sendable));
        $headers->addTextHeader('X-Sendable-Id', (string) $sendable->getKey());
        $headers->addTextHeader('X-Receivable-Type', (string) get_class($receivable));
        $headers->addTextHeader('X-Receivable-Id', (string) $receivable->getKey());
        $headers->addTextHeader('X-Email-Variant-Id', (string) $chosenEmailVariant->getKey());

        if ($eventContext !== null) {
            $headers->addTextHeader('X-Event-Context', json_encode($eventContext));
        }
    }

    /**
     * Remove all configured email headers
     */
    public function removeConfiguredEmailHeaders()
    {
        $headers = $this->email->getHeaders();

        $customHeaders = [
            'X-Sendable-Type',
            'X-Sendable-Id',
            'X-Receivable-Type',
            'X-Receivable-Id',
            'X-Email-Variant-Id',
            'X-Event-Context',
        ];

        foreach ($customHeaders as $headerName) {
            if ($headers->has($headerName)) {
                $headers->remove($headerName);
            }
        }

        return true;
    }

    public function createOrGetMessageId(): string
    {
        $messageId = $this->getMessageId();

        if ($messageId === null) {
            $messageId = $this->createMessageId();
        }

        return $messageId;
    }

    public static function generateNewMessageId()
    {
        return str()->uuid() . '@' . (config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'localhost');
    }

    public function createMessageId(?string $messageId = null): string
    {
        if ($messageId === null) {
            $messageId = self::generateNewMessageId();
        }

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

    public function getSendable(): array
    {
        $sendableType = $this->email->getHeaders()->has('X-Sendable-Type')
            ? $this->email->getHeaders()->getHeaderBody('X-Sendable-Type')
            : null;

        $sendableId = $this->email->getHeaders()->has('X-Sendable-Id')
            ? $this->email->getHeaders()->getHeaderBody('X-Sendable-Id')
            : null;

        return [
            'type' => $sendableType,
            'id' => $sendableId,
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

    public function getEmailVariantId(): ?int
    {
        return $this->email->getHeaders()->has('X-Email-Variant-Id')
            ? (int) $this->email->getHeaders()->getHeaderBody('X-Email-Variant-Id')
            : null;
    }

    public function addUnsubscribeHeader(string $messageId): void
    {
        $unsubscribeUrl = URL::signedRoute('emails.unsubscribe', [
            'message_id' => $messageId,
        ]);

        $this->email->getHeaders()->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');

        $this->email->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
    }

    /**
     * Header: X-Sendable-Type is always available if email handler is used
     */
    public function isUsingEmailHandler(): bool
    {
        return $this->email->getHeaders()->has('X-Sendable-Type');
    }
}
