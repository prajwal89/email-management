<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Closure;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use SplFileInfo;
use Throwable;

// ? should we alow each campaign to run multiple times?
// we have to keep campaign_runs table separably
class CampaignManager
{
    /**
     * this is also used as DB table
     */
    public static function getAllReceivableGroups(): Collection
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

    public static function allReceivablesFroGroups(array $receivables): Collection
    {
        // *hint use query for getting
        // todo handle multiple types of receivables
        // ! this can be memory intensive as we are fetching all users
        $allReceivables = collect($receivables)
            ->map(function ($fqn) {
                return $fqn::getQuery()
                    ->select(['id', 'email'])
                    ->get();
            })
            ->flatten()
            ->unique('email');

        return $allReceivables;
    }

    /**
     * @var array An array of fully qualified names (FQNs)
     */
    public static function dispatch(EmailCampaign $emailCampaign, array $receivableGroups): bool
    {
        // todo validate if campaign is already ran

        // validate receivables
        try {
            collect($receivableGroups)->each(function ($groupFqn): void {
                if (!class_exists($groupFqn) || !method_exists($groupFqn, 'getQuery')) {
                    throw new Exception("Invalid group or missing `getQuery` method: {$groupFqn}");
                }
                $groupFqn::getQuery()->count();
            });
        } catch (Exception $e) {
            throw new Exception('Validation failed: ' . $e->getMessage(), 0, $e);
        }

        $allReceivables = self::allReceivablesFroGroups($receivableGroups);

        $handler = $emailCampaign->resolveEmailHandler();

        $batch = Bus::batch(
            $allReceivables->map(function (Model $receivable) use ($handler): Closure {
                return function () use ($handler, $receivable): void {
                    (new $handler($receivable))->sendEmail();
                };
            })->toArray()
        )->then(function (Batch $batch): void {
            // ! send notification
            Log::info('All emails were successfully sent.');
        })->catch(function (Batch $batch, Throwable $e): void {
            // ! send notification
            Log::error('Failed to send some emails: ' . $e->getMessage());
        })->finally(function (Batch $batch) use ($emailCampaign): void {
            $emailCampaign->update([
                'ended_on' => now(),
            ]);
        })->dispatch();

        $emailCampaign->update([
            'receivable_groups' => $receivableGroups,
            'batch_id' => $batch->id,
            'started_on' => now(),
        ]);

        return true;
    }
}
