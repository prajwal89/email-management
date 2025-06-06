<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Symfony\Component\Mime\Email;

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

        $headers = $this->email->getHeaders();
        $headers->addTextHeader('X-Eventable-Type', (string) get_class($eventable));
        $headers->addTextHeader('X-Eventable-Id', (string) $eventable->getKey());
        $headers->addTextHeader('X-Receivable-Type', (string) get_class($receivable));
        $headers->addTextHeader('X-Receivable-Id', (string) $receivable->getKey());

        if ($eventContext !== null) {
            $headers->addTextHeader('X-Event-Context', json_encode($eventContext));
        }

        return;
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
            if ($messageIdHeader instanceof \Symfony\Component\Mime\Header\IdentificationHeader) {
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
}
