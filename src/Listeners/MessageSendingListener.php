<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Exception;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Prajwal89\EmailManagement\Enums\RecipientType;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\HeadersManager;

/**
 * @see https://github.com/jdavidbakr/mail-tracker/blob/master/src/MailTracker.php
 */
class MessageSendingListener
{
    // todo: remove all headers configured while building the email
    public function handle(MessageSending $event): void
    {
        $headersManager = new HeadersManager($event->message);

        $message = $event->message;

        $headers = $message->getHeaders();

        try {
            DB::beginTransaction();

            $emailLog = EmailLog::query()->create([
                'message_id' => $headersManager->getMessageId(),

                // todo: from can have multiple values also
                'from' => $message->getFrom()[0]->getAddress(),

                'sendable_type' => $headersManager->getSendable()['type'],
                'sendable_id' => $headersManager->getSendable()['id'],

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
