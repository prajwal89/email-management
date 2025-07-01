<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Prajwal89\EmailManagement\Enums\RecipientType;
use Prajwal89\EmailManagement\Models\EmailLog;
use Symfony\Component\Mime\Email;

class EmailLogService
{
    public static function store(Email $message): EmailLog
    {
        $headersManager = new HeadersManager($message);

        // dd($headersManager->getEmailVariantSlug());

        try {
            DB::beginTransaction();

            $inReplyToHeaders = $headersManager->getInReplyToHeader();

            $emailLog = EmailLog::query()->create([
                'message_id' => $headersManager->getMessageId(),

                // todo: from can have multiple values also
                'from' => $message->getFrom()[0]->getAddress(),

                'sendable_type' => $headersManager->getSendable()['type'],
                'sendable_slug' => $headersManager->getSendable()['slug'],

                'receivable_type' => $headersManager->getReceivable()['type'],
                'receivable_id' => $headersManager->getReceivable()['id'],

                'email_variant_slug' => $headersManager->getEmailVariantSlug(),

                'subject' => $message->getSubject(),
                'html' => $message->getHtmlBody(),
                'text' => $message->getTextBody(),
                'headers' => $message->getHeaders()->toArray(),
                'context' => $headersManager->getEventContext(),

                ...!empty($inReplyToHeaders) ? [
                    'in_reply_to' => $inReplyToHeaders[0],
                ] : [],

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

        return $emailLog;
    }

    public static function update(EmailLog $emailLog, array $attributes)
    {
        $emailLog->update($attributes);

        return true;
    }

    public static function destroy(EmailLog $emailLog)
    {
        return $emailLog->delete();
    }
}
