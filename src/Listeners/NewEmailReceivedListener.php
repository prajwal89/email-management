<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use DirectoryTree\ImapEngine\Laravel\Events\MessageReceived;
use DirectoryTree\ImapEngine\MessageInterface;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\BounceParser;
use Prajwal89\EmailManagement\Models\EmailLog;

// todo this should be job as exception here will break the command execution
// and it will be more easy to track jobs

/**
 * Enhanced email listener that handles bounces, replies, and conversation threading
 *
 * @see https://imapengine.com/docs/laravel/usage
 */
class NewEmailReceivedListener
{

    public function handle(MessageReceived $event): void
    {
        $message = $event->message;

        $parser = new BounceParser;

        // we can mock this to test
        $result = $parser->parse($message->__toString());

        Log::info('Message', [
            'message' => $message->__toString(),
            'result' => $result,
        ]);
    }
}
