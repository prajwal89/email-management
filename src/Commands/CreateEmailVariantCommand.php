<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Enums\EmailContentType;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\EmailViewFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

/**
 * php artisan em:create-email-variant --sendable_type=EmailEvent
 */
class CreateEmailVariantCommand extends Command
{
    protected $signature = 'em:create-email-variant {--sendable_type=} {--sendable_slug=}';

    protected $description = 'Create a new email variant and associate it with an email event';

    public function handle(): int
    {
        $sendableType = $this->option('sendable_type');

        $allowedSendableTypes = [
            'EmailEvent',
            'EmailCampaign',
        ];

        if (!$sendableType) {
            $this->error('--sendable_type is required.');

            return 1;
        }

        if (!in_array($sendableType, $allowedSendableTypes)) {
            $this->error("Invalid sendable type: {$sendableType}. Allowed values are:");
            foreach ($allowedSendableTypes as $type) {
                $this->line(" - {$type}");
            }

            return 1;
        }

        $sendableModel = match ($sendableType) {
            'EmailEvent' => EmailEvent::class,
            'EmailCampaign' => EmailCampaign::class,
        };

        // Fetch all email events to search through them.
        $events = $sendableModel::query()->pluck('name', 'id');

        // Check if any events exist before proceeding.
        if ($events->isEmpty()) {
            warning('No email events found. Please create an event first using em:create-email-event');

            return self::FAILURE;
        }

        $slug = $this->option('sendable_slug');

        if ($slug) {
            $selectedSendable = $sendableModel::where('slug', $slug)->first();

            if (!$selectedSendable) {
                $this->error("No {$sendableType} found with slug '{$slug}'");

                return self::FAILURE;
            }

            info("Using provided sendable. Selected {$sendableType}: '{$selectedSendable->name}'");
        } else {
            // Fetch all email events to search through them.
            $events = $sendableModel::query()->pluck('name', 'id');

            // Check if any events exist before proceeding.
            if ($events->isEmpty()) {
                warning('No email events found. Please create an event first using em:create-email-event');

                return self::FAILURE;
            }

            // Use the 'search' prompt to find the parent email event.
            $eventId = search(
                label: 'Which email event does this variant belong to?',
                placeholder: 'Start typing to search for an event...',
                options: fn(string $value) => strlen($value) > 0
                    ? $sendableModel::where('name', 'like', "%{$value}%")->pluck('name', 'id')->all()
                    : $events->all(),
                scroll: 10
            );

            $selectedSendable = $sendableModel::find($eventId);

            if (!$selectedSendable) {
                warning('Invalid selection. Aborting command.');

                return self::FAILURE;
            }

            info("You have selected the event: '{$selectedSendable->name}'.");
        }

        $data = [
            'name' => text(
                label: 'What is the name for this new email variant?',
                placeholder: 'Version B',
                required: 'The variant name is required.'
            ),
            'exposure_percentage' => text(
                label: 'What is the exposure percentage for this variant?',
                placeholder: 'Enter a number between 0 and 100',
                required: true,
                default: '50',
                validate: fn(string $value) => match (true) {
                    !is_numeric($value) => 'The value must be a number.',
                    $value < 0 => 'The percentage cannot be less than 0.',
                    $value > 100 => 'The percentage cannot be greater than 100.',
                    default => null
                }
            ),
            'content_type' => select(
                label: 'What is the content type for default variant?',
                options: collect(EmailContentType::cases())->mapWithKeys(function ($case) {
                    return [$case->value => $case->getLabel()];
                })->toArray(),
                default: EmailContentType::MARKDOWN->value,
                required: true,
                validate: fn(string $value) => in_array(
                    $value,
                    collect(EmailContentType::cases())->map(fn($case) => $case->value)->toArray(),
                    true
                ) ? null : 'Invalid content type selected.'
            )
        ];

        $filePath = (new SeederFileManager(EmailVariant::class))
            ->setAttributes($data)
            ->setSendableType(get_class($selectedSendable))
            ->setSendableSlug($selectedSendable->slug)
            ->generateFile();

        $this->createEmailView(
            sendable: $selectedSendable,
            data: $data
        );

        $this->info("Created Email Variant Seeder file: {$filePath}");

        Artisan::call('em:seed-db');

        return self::SUCCESS;
    }

    public function createEmailView(
        Model $sendable,
        array $data
    ): void {
        $viewFile = (new EmailViewFileManager(
            forModel: $sendable,
            sendableSlug: $sendable->slug,
            variantSlug: str($data['name'])->slug()->toString()
        ))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Mail View file: {$viewFile}.php");
    }
}
