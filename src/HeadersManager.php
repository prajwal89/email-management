<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

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

        $this->addSendableHeaders($sendable);

        $returnPath = config('email-management.return_path');

        if ($returnPath) {
            $this->email->returnPath($returnPath);
        }

        $replyTo = config('email-management.reply_to');

        if ($replyTo) {
            $this->email->replyTo($replyTo);
        }

        $headers = $this->email->getHeaders();

        $headers->addTextHeader('X-Receivable-Type', (string) get_class($receivable));
        $headers->addTextHeader('X-Receivable-Id', (string) $receivable->getKey());

        $headers->addTextHeader('X-Email-Variant-Slug', (string) $chosenEmailVariant->slug);

        if ($eventContext !== null) {
            $headers->addTextHeader('X-Event-Context', json_encode($eventContext));
        }

        return $this;
    }

    public function addSendableHeaders(EmailSendable $sendable)
    {
        $headers = $this->email->getHeaders();

        $headers->addTextHeader('X-Sendable-Type', (string) get_class($sendable));

        $headers->addTextHeader('X-Sendable-Slug', (string) $sendable->getKey());
    }

    public function addInReplyToHeader(string|array $inReplyTo)
    {
        if (is_array($inReplyTo)) {
            // Use the last message ID for In-Reply-To
            $this->email->getHeaders()->addIdHeader('In-Reply-To', end($inReplyTo));

            // Optionally add all previous message IDs to the References header
            $this->email->getHeaders()->addTextHeader('References', implode(' ', $inReplyTo));
        } else {
            $this->email->getHeaders()->addIdHeader('In-Reply-To', $inReplyTo);
        }

        return $this;
    }

    public function getInReplyToHeader()
    {
        return $this->email->getHeaders()->has('In-Reply-To')
            ? $this->email->getHeaders()->getHeaderBody('In-Reply-To')
            : null;
    }

    /**
     * Remove all configured email headers
     */
    public function removeConfiguredEmailHeaders()
    {
        $headers = $this->email->getHeaders();

        $customHeaders = [
            'X-Sendable-Type',
            'X-Sendable-Slug',
            'X-Receivable-Type',
            'X-Receivable-Id',
            'X-Email-Variant-Slug',
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

        $this->email->getHeaders()->addIdHeader('Message-Id', $messageId);

        return $messageId;
    }

    public function getMessageId(): ?string
    {
        $headers = $this->email->getHeaders();

        $messageId = null;

        // Correct way to get Message-Id
        if ($headers->has('Message-Id')) {
            $messageIdHeader = $headers->get('Message-Id');
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

        $sendableSlug = $this->email->getHeaders()->has('X-Sendable-Slug')
            ? $this->email->getHeaders()->getHeaderBody('X-Sendable-Slug')
            : null;

        return [
            'type' => $sendableType,
            'slug' => $sendableSlug,
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

    public function getEmailVariantSlug(): ?string
    {
        return $this->email->getHeaders()->has('X-Email-Variant-Slug')
            ? (string) $this->email->getHeaders()->getHeaderBody('X-Email-Variant-Slug')
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
