<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;

class EmailEventService
{
    public static function destroy(EmailEvent $emailEvent): bool
    {
        $seederPath = self::findSeederFile($emailEvent);
        $handlerPath = self::findEmailHandlerClass($emailEvent);
        $emailClassFilePath = self::findEmailClassFile($emailEvent);
        $emailViewFilePath = self::findEmailViewFile($emailEvent);

        $emailEvent->load('sentEmails.emailVisits');

        try {
            DB::beginTransaction();

            // ! this can become heavy in terms of total queries
            $emailEvent->sentEmails->map(function (EmailLog $sentEmail): void {
                SentEmailService::destroy($sentEmail);
            });

            $emailEvent->delete();

            File::delete([
                $seederPath,
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

    public static function findSeederFile(EmailEvent $emailEvent): string
    {
        $seederClassName = str($emailEvent->slug)->studly() . 'Seeder';

        $seederPath = __DIR__ . "/../../database/seeders/EmailEvents/{$seederClassName}.php";

        if (!File::exists($seederPath)) {
            throw new Exception('No matching Seeder file found.');
        }

        return $seederPath;
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
