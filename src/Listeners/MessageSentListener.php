<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Prajwal89\EmailManagement\Models\SentEmail;
use Symfony\Component\Mime\Email;

class MessageSentListener
{
    public function handle(MessageSent $event): void
    {
        /** @var Email $sentMessage */
        $sentMessage = $event->message;

        $headers = $sentMessage->getHeaders();

        // todo add flag as email is sent
        $sentEmail = SentEmail::query()
            ->where('hash', $headers->getHeaderBody('X-Mailer-Hash'))
            ->first();

        if (!$sentEmail) {
            // Optional: log or handle missing DB entry
            return;
        }

        $sentEmail->update([
            'message_id' => $event->sent->getMessageId(),
        ]);
    }
}
