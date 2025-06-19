<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class EmailCampaignService
{
    // todo: delete runs
    public static function destroy(EmailCampaign $emailCampaign): bool
    {
        $seederPath = self::findSeederFile($emailCampaign);
        $handlerPath = self::findEmailHandlerClass($emailCampaign);
        $emailClassFilePath = self::findEmailClassFile($emailCampaign);
        $emailViewFilePath = self::findEmailViewFile($emailCampaign);

        $emailCampaign->load('sentEmails.emailVisits');

        try {
            DB::beginTransaction();

            // ! this can become heavy in terms of total queries
            $emailCampaign->sentEmails->map(function (EmailLog $sentEmail): void {
                SentEmailService::destroy($sentEmail);
            });

            $emailCampaign->delete();

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

    public static function findSeederFile(EmailCampaign $emailCampaign): string
    {
        $seederClassName = str($emailCampaign->slug)->studly() . 'Seeder';

        $seederPath = __DIR__ . "/../../database/seeders/EmailCampaigns/{$seederClassName}.php";

        if (!File::exists($seederPath)) {
            throw new Exception('No matching Seeder file found.');
        }

        return $seederPath;
    }

    public static function findEmailHandlerClass(EmailCampaign $emailCampaign): string
    {
        $emailHandlerClassName = str($emailCampaign->slug)->studly() . 'EmailHandler';

        $handlerFilePath = __DIR__ . "/../../src/MailHandlers/EmailCampaigns/{$emailHandlerClassName}.php";

        if (!File::exists($handlerFilePath)) {
            throw new Exception('No EmailHandler found.');
        }

        return $handlerFilePath;
    }

    public static function findEmailClassFile(EmailCampaign $emailCampaign): string
    {
        $emailHandlerClassName = str($emailCampaign->slug)->studly() . 'Email';

        $mailClassPath = __DIR__ . "/../../src/Mails/EmailCampaigns/{$emailHandlerClassName}.php";

        if (!File::exists($mailClassPath)) {
            throw new Exception("$mailClassPath Email class file not found");
        }

        return $mailClassPath;
    }

    public static function findEmailViewFile(EmailCampaign $emailCampaign): string
    {
        $emailViewFileName = $emailCampaign->slug . '-email.blade.php';

        $mailViewPath = __DIR__ . "/../../resources/views/emails/email-campaigns/{$emailViewFileName}";

        if (!File::exists($mailViewPath)) {
            throw new Exception("$mailViewPath Email view file not found");
        }

        return $mailViewPath;
    }
}
