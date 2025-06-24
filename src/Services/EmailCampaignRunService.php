<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use App\Models\JobBatch;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailCampaignRun;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

class EmailCampaignRunService
{
    public static function destroy(EmailCampaignRun $emailCampaignRun): bool
    {
        DB::transaction(function () use ($emailCampaignRun) {
            $emailCampaignRun->jobBatch->map(function (JobBatch $jobBatch) {
                $jobBatch->delete();
            });

            $emailCampaignRun->delete();
        });


        return true;
    }
}
