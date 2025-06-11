<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Exception;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Prajwal89\EmailManagement\Enums\RecipientType;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailLogService;
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

        // for the emails that are not sent from the handler
        // todo: where to do content modification
        if ($headersManager->getMessageId() == null) {
            $headersManager->createMessageId();
        }

        EmailLogService::store($message);
    }
}
