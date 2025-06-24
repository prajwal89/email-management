<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailCampaignRun;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

class EmailCampaignService
{
    public static function destroy(EmailCampaign $emailCampaign): bool
    {
        // Do not delete seeder file as they will be deleted in pairs
        DB::transaction(function () use ($emailCampaign) {
            $emailCampaign->load('emailLogs.emailVisits');

            $emailCampaign->emailVariants()->get()->map(function (EmailVariant $emailVariant): void {
                EmailVariantService::destroy($emailVariant);
            });

            // ! this can become heavy in terms of total queries
            $emailCampaign->emailLogs()->get()->map(function (EmailLog $emailLog): void {
                EmailLogService::destroy($emailLog);
            });

            $emailCampaign->runs()->get()->map(function (EmailCampaignRun $emailCampaignRun): void {
                EmailCampaignRunService::destroy($emailCampaignRun);
            });

            (new SeederFileManager($emailCampaign))
                ->setSendableType(EmailCampaign::class)
                ->setSendableSlug($emailCampaign->slug)
                ->generateDeleteSeederFile();

            // delete email handler
            $handlerPath = EmailCampaign::getEmailHandlerFilePath($emailCampaign->slug);

            // deleting mailable class from here
            // b.c mailable class is one per sendable
            // and email variant does not delete the mailable classes
            $mailableClassPath = EmailCampaign::getMailableClassPath($emailCampaign->slug);

            File::delete([
                $handlerPath,
                $mailableClassPath,
            ]);
        });

        return true;
    }
}
