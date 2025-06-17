<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

class EmailEventService
{
    public static function destroy(EmailEvent $emailEvent): bool
    {
        // Do not delete seeder file as they will be deleted in pairs
        DB::transaction(function () use ($emailEvent) {
            $handlerPath = self::findEmailHandlerClass($emailEvent);

            $emailEvent->load('sentEmails.emailVisits');

            DB::beginTransaction();

            $emailEvent->emailVariants()->map(function (EmailVariant $emailVariant): void {
                EmailVariantService::destroy($emailVariant);
            });

            // ! this can become heavy in terms of total queries
            $emailEvent->emailLogs()->map(function (EmailLog $emailLog): void {
                EmailLogService::destroy($emailLog);
            });

            (new SeederFileManager($emailEvent))->generateDeleteSeederFile();

            // this will effectively delete the email event record
            $exitCode = Artisan::call('em:seed-db');

            if ($exitCode !== 0) {
                throw new Exception("Command 'php artisan em:seed-db' Failed " . $exitCode);
            }

            File::delete([$handlerPath]);
        });

        return true;
    }

    public static function findEmailHandlerClass(EmailEvent $emailEvent): string
    {
        $emailHandlerClassName = str($emailEvent->slug)->studly() . 'EmailHandler';

        $handlerFilePath = __DIR__ . "/../../src/MailHandlers/EmailEvents/{$emailHandlerClassName}.php";

        if (!File::exists($handlerFilePath)) {
            throw new Exception('No EmailHandler found.');
        }

        return $handlerFilePath;
    }
}
