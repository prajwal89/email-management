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


        // follow up for these sendable
        // b.c EmailEvent,and EmailCampaign can have follow ups
        $followupableType = select(
            label: 'Followupable Model Class',
            options: [
                EmailEvent::class,
                EmailCampaign::class,
            ],
            hint: 'Follow up for these sendable',
            required: true,
            validate: ['required', 'string']
        );

        $followupableId = select(
            label: 'Followupable ID',
            options: EmailEvent::query()
                ->where('is_followup_email', 0)
                ->pluck('name', 'id'),
            required: true,
            validate: ['required', 'integer', 'min:1']
        );



        $allFollowupEmails = EmailEvent::query()
            ->where('is_followup_email', 1)
            ->get();

        if ($allFollowupEmails->isEmpty()) {
            throw new Exception("There are no follow up emailEvents please create email event");
        }

        // the email that will be sent
        // todo: add constraint of suffix of FollowUp
        // follow up emails are emailevents only 
        $sendableId = select(
            label: 'Sendable name',
            options: $allFollowupEmails->pluck('name', 'id'),
            required: true,
            validate: ['required', 'integer', 'min:1']
        );



        // todo check if there are already follow ups for 
        $waitForHours = text(
            label: 'Wait time (in hours) before sending this follow-up',
            required: true,
            validate: ['required', 'integer', 'min:1']
        );


        $data = [
            'is_enabled' => select(
                label: 'Enable follow-up?',
                options: [
                    1 => 'Yes',
                    0 => 'No'
                ],
                default: 1
            ),
        ];

        $validator = Validator::make($data, [
            'sendable_type' => ['required', 'string'],
            'sendable_id' => ['required', 'integer', 'min:1'],
            'followupable_type' => ['required', 'string'],
            'followupable_id' => ['required', 'integer', 'min:1'],
            'wait_for_hours' => ['required', 'integer', 'min:1'],
            'is_enabled' => ['required', Rule::in([0, 1])],
        ]);

        if ($validator->fails()) {
            $this->error("Invalid input:\n" . $validator->errors()->toJson(JSON_PRETTY_PRINT));
            return;
        }

        $followUp = FollowUp::create([
            'sendable_type' => $data['sendable_type'],
            'sendable_id' => (int) $data['sendable_id'],
            'followupable_type' => $data['followupable_type'],
            'followupable_id' => (int) $data['followupable_id'],
            'wait_for_hours' => (int) $data['wait_for_hours'],
            'is_enabled' => (bool) $data['is_enabled'],
        ]);

        $this->info("✅ Follow-up created with ID: {$followUp->id}");
    }
}
