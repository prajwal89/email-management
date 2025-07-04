<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Prajwal89\EmailManagement\Services\EmailContentModifiers;
use Prajwal89\EmailManagement\Services\EmailLogService;
use Prajwal89\EmailManagement\HeadersManager;

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
        if (!$headersManager->isUsingEmailHandler()) {
            if ($headersManager->getMessageId() === null) {
                $headersManager->createMessageId();
            }

            // todo: do content modification

            // $emailContentModifiers = new EmailContentModifiers(
            //     $event->message,
            //     $headersManager->getMessageId()
            // );

            // if (config('email-management.track_visits')) {
            //     $emailContentModifiers->injectTrackingUrls();
            // }

            // if (config('email-management.track_opens')) {
            //     $emailContentModifiers->injectTrackingPixel();
            // }

            // if (config('email-management.inject_unsubscribe_link')) {
            //     $emailContentModifiers->injectUnsubscribeLink();
            // }
        }

        EmailLogService::store($message);
    }
}
