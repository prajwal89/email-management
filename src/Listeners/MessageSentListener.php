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
        //
        /** @var Email $sentMessage */
        $sentMessage = $event->message;

        $headers = $sentMessage->getHeaders();
        // dump($headers);

        $hash = $headers->getHeaderBody('X-Mailer-Hash');

        $sentEmail = SentEmail::query()->where('hash', $hash)->first();

        // todo add flag as email is sent
        // dd($sentEmail->toArray());

        if ($sentEmail) {
            // dd($sentEmail);
        }
    }
}
