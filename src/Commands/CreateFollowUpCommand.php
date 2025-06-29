<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Exception;
use Illuminate\Console\Command;
use Prajwal89\EmailManagement\FileManagers\Migrations\FollowUpMigration;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\FollowUp;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

/**
 * Create a new follow-up for a sendable
 *
 * Usage:
 * php artisan em:create-follow-up
 * php artisan em:create-follow-up --type="Prajwal89\EmailManagement\Models\EmailEvent" --slug="welcome-email"
 */
class CreateFollowUpCommand extends Command
{
    protected $signature = 'em:create-follow-up 
                            {--type= : The followupable type (EmailEvent or EmailCampaign class)}
                            {--slug= : The followupable slug}';

    protected $description = 'Create a new follow-up for a sendable';

    public function handle(): void
    {
        // Get followupable type from option or prompt
        $followupableType = $this->getFollowupableType();

        // Get followupable slug from option or prompt
        $followupableSlug = $this->getFollowupableSlug($followupableType);

        // Get all available follow-up email events
        $allFollowupEmailEvents = $this->getFollowupEmailEvents();

        $followupable = $followupableType::query()
            ->with(['followUps' => function ($query) {
                $query->orderBy('wait_for_days', 'asc');
            }, 'followUps.followupEmailEvent'])
            ->where('slug', $followupableSlug)
            ->first();

        if (!$followupable) {
            throw new Exception("No {$followupableType} found with slug: {$followupableSlug}");
        }

        $largestFollowUpDays = 0;

        if ($followupable->followUps->isNotEmpty()) {
            $this->info('Current Follow ups');
            $this->info('Note: Next Follow up needs bigger delay');
            // so we need to add next follow up with the bigger gap
            $largestFollowUpDays = $followupable->followUps->max('wait_for_days');

            foreach ($followupable->followUps as $i => $followUp) {
                $this->info(++$i . " : {$followUp->followupEmailEvent->name} (+{$followUp->wait_for_days} hours)");
            }
        }

        // the email that will be sent
        // todo: add constraint of suffix of FollowUp
        // follow up emails are emailevents only
        $emailEventId = select(
            label: 'Choose follow up email',
            hint: 'Email event for follow up email',
            options: $allFollowupEmailEvents->pluck('name', 'id'),
            required: true,
            validate: [
                'required',
                'integer',
                'min:1',
            ]
        );

        // todo check if there are already follow ups for
        // this should have max time delay so we can filter out emails logs that needs to sent
        // follow up emails
        $options = [];

        foreach (range(1, config('email-management.max_delay_for_followup_email')) as $day) {
            if ($day <= $largestFollowUpDays) {
                continue;
            }
            $options[$day] = "{$day} day(s)";
        }

        $waitForDays = select(
            label: 'Wait time (in hours) before sending this follow-up (1 day = 24 hours)',
            options: $options,
            default: 24
        );

        // $isEnabled =  confirm(
        //     label: 'Enable follow-up?',
        //     default: false
        // ) ? 1 : 0;

        $data = [
            'followup_email_event_id' => $emailEventId,
            'followupable_type' => $followupableType,
            'followupable_id' => $followupable->id,
            'wait_for_days' => $waitForDays,
            // 'is_enabled' => (bool) $isEnabled,
            'is_enabled' => true,
        ];

        $filePath = (new FollowUpMigration(
            // forModel: FollowUp::class,
            modelAttributes: $data,
            followupAbleEvent: EmailEvent::find($emailEventId),
            followupAble: $followupable
        ))
            ->generateFile();

        $this->info("Created Migration file: {$filePath}");

        if ($this->confirm('Do you want to run the migration now?', true)) {
            $this->call('migrate');
        } else {
            $this->info('You can run it later with: php artisan migrate');
        }
    }

    // public function createMigrationFile(array $data): void
    // {
    //     $filePath = (new FollowUpMigration(
    //         forModel: FollowUp::class,
    //         modelAttributes: $data
    //     ))
    //         ->generateFile();

    //     $this->info("Created Migration file: {$filePath}");
    // }

    /**
     * Get the followupable type from option or prompt user
     */
    private function getFollowupableType(): string
    {
        $type = $this->option('type');

        if ($type) {
            // Validate the provided type
            $validTypes = [EmailEvent::class, EmailCampaign::class];

            if (!in_array($type, $validTypes)) {
                throw new Exception("Invalid type: {$type}. Valid types are: " . implode(', ', $validTypes));
            }

            return $type;
        }

        // Prompt user if not provided
        return select(
            label: 'For which followupable type?',
            options: [
                EmailEvent::class,
                EmailCampaign::class,
            ],
            required: true,
            validate: ['required', 'string']
        );
    }

    /**
     * Get the followupable slug from option or prompt user
     */
    private function getFollowupableSlug(string $followupableType): string
    {
        $slug = $this->option('slug');

        if ($slug) {
            // Check if the record exists
            $exists = $followupableType::query()
                ->when($followupableType === EmailEvent::class, function ($query) {
                    $query->where('is_followup_email', 0);
                })
                ->where('slug', $slug)
                ->exists();

            if (!$exists) {
                throw new Exception("No {$followupableType} found with slug: {$slug}");
            }

            return $slug;
        }

        // Prompt user if not provided
        return select(
            label: 'For which followupable name?',
            options: $followupableType::query()
                ->when($followupableType === EmailEvent::class, function ($query) {
                    $query->where('is_followup_email', 0);
                })
                ->latest()
                ->pluck('name', 'slug'),
            required: true,
            validate: ['required', 'string']
        );
    }

    /**
     * Get all available follow-up email events
     */
    private function getFollowupEmailEvents()
    {
        $allFollowupEmailEvents = EmailEvent::query()
            ->latest()
            ->where('is_followup_email', 1)
            ->get();

        if ($allFollowupEmailEvents->isEmpty()) {
            throw new Exception('There are no follow up emailEvents. Please create email event first.');
        }

        return $allFollowupEmailEvents;
    }
}
