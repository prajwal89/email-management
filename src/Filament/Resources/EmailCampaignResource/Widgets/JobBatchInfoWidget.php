<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class JobBatchInfoWidget extends BaseWidget
{
    public EmailCampaign $record;

    protected function getStats(): array
    {
        return [];
        $this->record->load('jobBatch');

        if (empty($this->record->jobBatch)) {
            return [];
        }

        return [
            // Stat::make('Total Emails', $this->record->jobBatch->total_jobs)
            //     ->color('primary'),  

            Stat::make('Pending Emails', $this->record->jobBatch->pending_jobs)
                ->color('warning'),

            Stat::make('Failed Emails', $this->record->jobBatch->failed_jobs)
                ->color('danger'),
        ];
    }
}
