<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use App\Models\JobBatch;
use Illuminate\Support\Facades\DB;
use Prajwal89\EmailManagement\Models\EmailCampaignRun;

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
