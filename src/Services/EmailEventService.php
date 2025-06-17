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
        $handlerPath = self::findEmailHandlerClass($emailEvent);
        $emailClassFilePath = self::findEmailClassFile($emailEvent);
        $emailViewFilePath = self::findEmailViewFile($emailEvent);

        $emailEvent->load('sentEmails.emailVisits');

        // Do not delete seeder file as they will be deleted in pairs
        try {
            DB::beginTransaction();

            $emailEvent->emailVariants()->map(function (EmailVariant $emailVariant): void {
                EmailVariantService::destroy($emailVariant);
            });

            // ! this can become heavy in terms of total queries
            $emailEvent->emailLogs()->map(function (EmailLog $emailLog): void {
                EmailLogService::destroy($emailLog);
            });

            $emailEvent->delete();

            (new SeederFileManager($emailEvent))->generateDeleteSeederFile();

            $exitCode = Artisan::call('em:seed-db');

            if ($exitCode !== 0) {
                throw new Exception("command php artisan em:seed-db Failed " . $exitCode);
            }

            File::delete([
                $handlerPath,
                $emailClassFilePath,
                $emailViewFilePath,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

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

    public static function findEmailClassFile(EmailEvent $emailEvent): string
    {
        $emailHandlerClassName = str($emailEvent->slug)->studly() . 'Email';

        $mailClassPath = __DIR__ . "/../../src/Mails/EmailEvents/{$emailHandlerClassName}.php";

        if (!File::exists($mailClassPath)) {
            throw new Exception("$mailClassPath Email class file not found");
        }

        return $mailClassPath;
    }

    public static function findEmailViewFile(EmailEvent $emailEvent): string
    {
        $emailViewFileName = $emailEvent->slug . '-email.blade.php';

        $mailViewPath = __DIR__ . "/../../resources/views/emails/email-events/{$emailViewFileName}";

        if (!File::exists($mailViewPath)) {
            throw new Exception("$mailViewPath Email view file not found");
        }

        return $mailViewPath;
    }
}
