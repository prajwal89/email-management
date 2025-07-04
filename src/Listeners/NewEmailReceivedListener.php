<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use DirectoryTree\ImapEngine\Laravel\Events\MessageReceived;
use DirectoryTree\ImapEngine\MessageInterface;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\BounceParser;
use Prajwal89\EmailManagement\Dtos\BounceDataDto;
use Prajwal89\EmailManagement\Jobs\HandleNewEmailReceivedJob;
use Prajwal89\EmailManagement\Models\EmailLog;

/**
 * Enhanced email listener that handles bounces, replies, and conversation threading
 *
 * @see https://imapengine.com/docs/laravel/usage
 */
class NewEmailReceivedListener
{

    public function handle(MessageReceived $event)
    {
        $message = $event->message;

        dispatch(new HandleNewEmailReceivedJob($message));
    }
}
