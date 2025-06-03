<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Exception;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Prajwal89\EmailManagement\Enums\RecipientType;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailContentModifiers;
use Prajwal89\EmailManagement\Services\HeadersManager;
use Symfony\Component\Mime\Part\TextPart;

// todo add test for injected pixel and tracking urls

/**
 * @see https://github.com/jdavidbakr/mail-tracker/blob/master/src/MailTracker.php
 */
class MessageSendingListener
{
    public function handle(MessageSending $event): void
    {
        $headersManager = new HeadersManager($event->message);

        $message = $event->message;
        $headers = $message->getHeaders();

        $messageId = $headersManager->createMessageId();

        // EmailContentModifiers::removeHeaders($headers);
        // EmailContentModifiers::addUnsubscribeHeader($headers, $hash);

        // $updatedHtml = str($message->getHtmlBody())
        //     ->pipe(function ($html) use ($hash): string {
        //         return EmailContentModifiers::injectTrackingUrls($html->toString(), $hash);
        //     })
        //     // todo enable this after testing (that email delivery is not affected)
        //     // ->pipe(function ($html) use ($hash) {
        //     //     return EmailContentModifiers::injectTrackingPixel($html->toString(), $hash);
        //     // })
        //     ->pipe(function ($html) use ($hash): string {
        //         return EmailContentModifiers::injectUnsubscribeLink($html->toString(), $hash);
        //     })
        //     ->toString();
        // $message->setBody(new TextPart($updatedHtml, 'utf-8', 'html'));

        try {
            DB::beginTransaction();

            $emailLog = EmailLog::query()->create([
                'message_id' => $messageId,

                // todo: from can have multiple values also
                'from' => $message->getFrom()[0]->getAddress(),

                'eventable_type' => $headersManager->getEventable()['type'],
                'eventable_id' => $headersManager->getEventable()['id'],
                'receivable_type' => $headersManager->getReceivable()['type'],
                'receivable_id' => $headersManager->getReceivable()['id'],

                'subject' => $message->getSubject(),
                'html' => $message->getHtmlBody(),
                'text' => $message->getTextBody(),
                'headers' => $headers->toArray(),
                'context' => $headersManager->getEventContext(),

                // todo: add this
                // 'mailer' => $mailerName,
                // 'transport' => $transportName,
            ]);

            foreach ($message->getTo() as $i => $to) {
                $emailLog->recipients()->create([
                    'type' => RecipientType::TO,
                    'email' => $to->getAddress(),
                ]);
            }

            foreach ($message->getCc() as $i => $cc) {
                $emailLog->recipients()->create([
                    'type' => RecipientType::CC,
                    'email' => $cc->getAddress(),
                ]);
            }

            foreach ($message->getBcc() as $i => $bcc) {
                $emailLog->recipients()->create([
                    'type' => RecipientType::BCC,
                    'email' => $bcc->getAddress(),
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
