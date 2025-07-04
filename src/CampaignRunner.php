<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Jobs\SendCampaignMailJob;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use SplFileInfo;
use Throwable;

// ? should we alow each campaign to run multiple times?
// we have to keep campaign_runs table separably
// todo: add support for delay between emails
class CampaignRunner
{
    public function __construct(
        public EmailCampaign $emailCampaign,
        public array $receivableGroups,
        public int $delayBetweenJobs = 5, // in sec
    ) {
        //
    }

    public function run()
    {
        $emailHandler = $this->emailCampaign->resolveEmailHandler();

        $allReceivables = $this->allReceivablesWithUniqueEmail();

        $allEmailJobs = $allReceivables
            ->map(function (EmailReceivable $receivable, int $index) use ($emailHandler) {
                if ($this->delayBetweenJobs === 0) {
                    return new SendCampaignMailJob($emailHandler, $receivable);
                }

                return (new SendCampaignMailJob($emailHandler, $receivable))
                    ->delay(now()->addSeconds($index * $this->delayBetweenJobs));
            });

        $campaignRun = $this->emailCampaign->runs()->create([
            'receivable_groups' => $this->receivableGroups,
        ]);

        $batch = Bus::batch($allEmailJobs->toArray())
            ->then(function (Batch $batch): void {
                Log::info('All emails were successfully sent.');
            })
            ->catch(function (Batch $batch, Throwable $e): void {
                Log::error('Failed to send some emails: ' . $e->getMessage());
            })
            ->finally(function (Batch $batch) {
                // todo: success event
            })
            ->dispatch();

        dd($batch);

        $campaignRun->update(['batch_id' => $batch->id]);

        return $batch;
    }

    public function allReceivablesWithUniqueEmail()
    {
        return collect($this->receivableGroups)
            ->flatMap(function ($fqn) {
                return $fqn::getQuery()->select(['email'])->get();
            })
            ->unique('email')
            ->values();
    }

    /**for livewire component */
    public static function allGroupsData(): Collection
    {
        return collect(
            File::allFiles(config('email-management.receivable_groups_path'))
        )->map(function (SplFileInfo $file): array {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $fqn = 'App\\EmailManagement\\ReceivableGroups\\' . $className;

            return [
                'classname' => $className,
                'FQN' => $fqn,
                'total' => $fqn::getQuery()->count(),
                'description' => $fqn::getDescription(),
            ];
        });
    }
}
