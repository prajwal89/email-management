<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Prajwal89\EmailManagement\Models\SentEmail;
use Prajwal89\EmailManagement\Services\EmailContentModifiers;
use Symfony\Component\Mime\Part\TextPart;

// todo add test for injected pixel and tracking urls

/**
 * @see https://github.com/jdavidbakr/mail-tracker/blob/master/src/MailTracker.php
 */
class MessageSendingListener
{
    public function handle(MessageSending $event): void
    {
        $message = $event->message;

        $headers = $message->getHeaders();

        if (!$headers->has('X-Mailer-Hash')) {
            $hash = EmailContentModifiers::attachMailerHashHeader($headers);
        } else {
            $hash = $headers->getHeaderBody('X-Mailer-Hash');
        }

        $eventContext = $headers->has('X-Event-Context') ? $headers->getHeaderBody('X-Event-Context') : null;

        $eventableType = $headers->has('X-Eventable-Type') ? $headers->getHeaderBody('X-Eventable-Type') : null;
        $eventableId = $headers->has('X-Eventable-Id') ? $headers->getHeaderBody('X-Eventable-Id') : null;
        $receivableType = $headers->has('X-Receivable-Type') ? $headers->getHeaderBody('X-Receivable-Type') : null;
        $receivableId = $headers->has('X-Receivable-Id') ? $headers->getHeaderBody('X-Receivable-Id') : null;

        EmailContentModifiers::removeHeaders($headers);
        EmailContentModifiers::addUnsubscribeHeader($headers, $hash);

        $updatedHtml = str($message->getHtmlBody())
            ->pipe(function ($html) use ($hash): string {
                return EmailContentModifiers::injectTrackingUrls($html->toString(), $hash);
            })
            // todo enable this after testing (that email delivery is not affected)
            // ->pipe(function ($html) use ($hash) {
            //     return EmailContentModifiers::injectTrackingPixel($html->toString(), $hash);
            // })
            ->pipe(function ($html) use ($hash): string {
                return EmailContentModifiers::injectUnsubscribeLink($html->toString(), $hash);
            })
            ->toString();

        $message->setBody(new TextPart($updatedHtml, 'utf-8', 'html'));

        SentEmail::query()->create([
            'hash' => $hash,
            'eventable_type' => $eventableType,
            'eventable_id' => $eventableId,
            'receivable_type' => $receivableType,
            'receivable_id' => $receivableId,
            'subject' => $message->getSubject(),
            // ! We are assuming single recipient and senderEmail
            'sender_email' => $message->getFrom()[0]->getAddress() ?? null,
            'recipient_email' => $message->getTo()[0]->getAddress() ?? null,
            'email_content' => $updatedHtml,
            'headers' => $headers->toArray(),
            'context' => $eventContext ? json_decode($eventContext, true) : null,
        ]);
    }
}
