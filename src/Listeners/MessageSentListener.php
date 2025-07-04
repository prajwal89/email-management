<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailLogService;
use Prajwal89\EmailManagement\HeadersManager;

class MessageSentListener
{
    public function handle(MessageSent $event): void
    {
        $headersManager = new HeadersManager($event->message);

        $emailLog = EmailLog::query()
            ->where('message_id', $headersManager->getMessageId())
            ->first();

        if (!$emailLog) {
            return;
        }

        EmailLogService::update($emailLog, ['sent_at' => now()]);
    }
}
