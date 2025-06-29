<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\FileManagers\MigrationFileManager;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Models\FollowUp;

class EmailEventService
{
    // ask user to run migration
    public static function destroy(EmailEvent $emailEvent): bool
    {
        // Do not delete seeder file as they will be deleted in pairs
        DB::transaction(function () use ($emailEvent) {
            $emailEvent->load('emailLogs.emailVisits');

            $emailEvent->emailVariants()->get()->map(function (EmailVariant $emailVariant): void {
                EmailVariantService::destroy($emailVariant);
            });

            // ! this can become heavy in terms of total queries
            $emailEvent->emailLogs()->get()->map(function (EmailLog $emailLog): void {
                EmailLogService::destroy($emailLog);
            });

            $emailEvent->followUps()->get()->map(function (FollowUp $followUp) {
                FollowUpService::destroy($followUp);
            });

            (new MigrationFileManager($emailEvent))
                ->setSendableType(EmailEvent::class)
                ->setSendableSlug($emailEvent->slug)
                ->generateDeleteSeederFile();

            // delete email handler
            $handlerPath = EmailEvent::getEmailHandlerFilePath($emailEvent->slug);

            // deleting mailable class from here
            // b.c mailable class is one per sendable
            // and email variant does not delete the mailable classes
            $mailableClassPath = EmailEvent::getMailableClassPath($emailEvent->slug);

            File::delete([
                $handlerPath,
                $mailableClassPath,
            ]);
        });

        return true;
    }
}
