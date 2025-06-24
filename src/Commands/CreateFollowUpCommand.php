<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\FollowUp;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Pest\Laravel\options;

/**
 * php artisan em:create-follow-up
 */
class CreateFollowUpCommand extends Command
{
    protected $signature = 'em:create-follow-up';

    protected $description = 'Create a new follow-up for a sendable';

    public function handle(): void
    {
        // $sendableType = select(
        //     label: 'Sendable Model Class',
        //     hint: 'E.g. App\\Models\\EmailEvent',
        //     options: [
        //         EmailEvent::class,
        //         EmailCampaign::class,
        //     ],
        //     required: true,
        //     validate: ['required', 'string']
        // );


        // follow up for this sendable
        // b.c EmailEvent,and EmailCampaign can have follow ups
        $followupableType = select(
            label: 'For which followupable type?',
            options: [
                EmailEvent::class,
                EmailCampaign::class,
            ],
            // hint: '',
            required: true,
            validate: ['required', 'string']
        );

        $followupableId = select(
            label: 'For which followupable name?',
            options: EmailEvent::query()
                ->where('is_followup_email', 0)
                ->latest()
                ->pluck('name', 'id'),
            required: true,
            validate: ['required', 'integer', 'min:1']
        );


        // move this logic
        $allFollowupEmailEvents = EmailEvent::query()
            ->latest()
            ->where('is_followup_email', 1)
            ->get();

        if ($allFollowupEmailEvents->isEmpty()) {
            throw new Exception("There are no follow up emailEvents please create email event");
        }


        $followupable = $followupableType::query()
            ->with(['followUps' => function ($query) {
                $query->orderBy('wait_for_days', 'asc');
            }, 'followUps.followupEmailEvent'])
            ->where('id', $followupableId)
            ->first();

        $largestFollowUpDays = 0;

        if ($followupable->followUps->isNotEmpty()) {
            $this->info("Current Follow ups");
            $this->info("Note: Next Follow up needs bigger delay");
            // so we need to add next follow up with the bigger gap
            $largestFollowUpDays = $followupable->followUps->max('wait_for_days');

            foreach ($followupable->followUps as $i => $followUp) {
                $this->info(++$i . " : {$followUp->followupEmailEvent->name} (+{$followUp->wait_for_days} hours)");
            }

            // $this->info("");
        }

        // dd($followupable->followUps);

        // the email that will be sent
        // todo: add constraint of suffix of FollowUp
        // follow up emails are emailevents only 
        $emailEventId = select(
            label: 'Choose follow up email',
            hint: "Email event for follow up email",
            options: $allFollowupEmailEvents->pluck('name', 'id'),
            required: true,
            validate: [
                'required',
                'integer',
                'min:1'
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

        // ?we should create the migration for this
        $followUp = FollowUp::create([
            'followup_email_event_id' => $emailEventId,
            'followupable_type' => $followupableType,
            'followupable_id' => $followupableId,
            'wait_for_days' => $waitForDays,
            // 'is_enabled' => (bool) $isEnabled,
            'is_enabled' => true,
        ]);

        $this->info("✅ Follow-up created with ID: {$followUp->id}");
    }
}
