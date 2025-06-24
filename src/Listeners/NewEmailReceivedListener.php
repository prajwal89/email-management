<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use DirectoryTree\ImapEngine\Laravel\Events\MessageReceived;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailLog;

class NewEmailReceivedListener
{
    public function handle(MessageReceived $event): void
    {
        $message = $event->message;

        // If it has inReplyTo this means the email is a reply to our previously sent email
        if ($message->inReplyTo()) {
            $inReplyToMessageId = $message
                ->inReplyTo()
                ->email();

            EmailLog::query()
                ->where('message_id', $inReplyToMessageId)
                ->first()
                ?->update([
                    'in_reply_to' => $inReplyToMessageId,
                    'replied_at' => $message->date(),
                ]);
        }

        // todo track references header

        // Log::info("NewEmailReceivedListener", [
        //     'from' => $message->from()->email(),
        //     'subject' => $message->subject(),
        //     'headers' => $message->headers(),
        //     'inReplyToMessageId' => $inReplyToMessageId,
        //     'in_reply_to' => $message->inReplyTo()->email(),
        // ]);
    }
}
