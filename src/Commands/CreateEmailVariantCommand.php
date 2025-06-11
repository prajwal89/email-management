<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;

use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

/**
 * pa email-management:create-email-variant
 */
class CreateEmailVariantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-management:create-email-variant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new email variant and associate it with an email event';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Fetch all email events to search through them.
        $events = EmailEvent::query()->pluck('name', 'id');

        // Check if any events exist before proceeding.
        if ($events->isEmpty()) {
            warning('No email events found. Please create an event first using email-management:create-email-event');

            return self::FAILURE;
        }

        // Use the 'search' prompt to find the parent email event.
        $eventId = search(
            label: 'Which email event does this variant belong to?',
            placeholder: 'Start typing to search for an event...',
            options: fn (string $value) => strlen($value) > 0
                ? EmailEvent::where('name', 'like', "%{$value}%")->pluck('name', 'id')->all()
                : $events->all(),
            scroll: 10
        );

        $selectedEvent = EmailEvent::find($eventId);

        if (!$selectedEvent) {
            warning('Invalid selection. Aborting command.');

            return self::FAILURE;
        }

        info("You have selected the event: '{$selectedEvent->name}'.");

        // --- New prompts for variant details ---

        // Ask for the variant's name
        $variantName = text(
            label: 'What is the name for this new email variant?',
            placeholder: 'e.g., Welcome Email - Version B',
            required: 'The variant name is required.'
        );

        // Ask for the exposure percentage with validation
        $exposurePercentage = text(
            label: 'What is the exposure percentage for this variant?',
            placeholder: 'Enter a number between 0 and 100',
            required: true,
            validate: fn (string $value) => match (true) {
                !is_numeric($value) => 'The value must be a number.',
                $value < 0 => 'The percentage cannot be less than 0.',
                $value > 100 => 'The percentage cannot be greater than 100.',
                default => null
            }
        );

        // Create the email variant record
        try {

            $this->createSeederFile(
                sendable: $selectedEvent,
                data: [
                    'name' => $variantName,
                    'exposure_percentage' => $exposurePercentage,
                ]
            );

            // todo: create view file
            $this->createEmailView(
                sendable: $selectedEvent,
                data: [
                    'name' => $variantName,
                    'exposure_percentage' => $exposurePercentage,
                ]
            );

            info("Successfully created variant: {$variantName}");
            // info("Successfully created variant '{$variant->name}' with {$variant->exposure_percentage}% exposure.");
        } catch (\Exception $e) {
            warning('Could not create the variant. A variant with the same slug might already exist.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    public function createSeederFile(
        EmailEvent|EmailCampaign $sendable,
        array $data
    ): void {
        $slug = str($data['name'])->slug();

        $seederClassName = str($sendable->slug)->studly() . $slug->studly() . 'Seeder';

        $seederStub = str(File::get(__DIR__ . '/../../stubs/email-variant-seeder.stub'))
            ->replace('{name}', $data['name'])
            ->replace('{slug}', $slug)
            ->replace('{exposure_percentage}', $data['exposure_percentage'])
            ->replace('{seeder_class_name}', $seederClassName)

            // ->replace('{sendable_class_name}', basename($sendable))
            ->replace('{sendable_type}', class_basename($sendable))
            ->replace('{sendable_slug}', $sendable->slug);

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailVariants';

        $filePath = $seederPath . "/{$seederFileName}";

        if (!File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        if (File::exists($filePath)) {
            $this->error("seeder file already exists: {$filePath}");

            return;
        }

        File::put($filePath, $seederStub);

        $this->info("Created seeder file: {$filePath}");
    }

    /**
     * markdown view for email
     */
    public function createEmailView(
        EmailEvent|EmailCampaign $sendable,
        array $data
    ): void {
        $variantSlug = str($data['name'])->slug();

        $emailViewFileName = $sendable->slug . '-' . $variantSlug . '-email.blade.php';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../stubs/email-markdown-view.stub'))
            ->replace('{name}', $data['name']);

        $folderName = match (get_class($sendable)) {
            EmailEvent::class => 'email-events',
            EmailCampaign::class => 'email-campaigns',
        };

        $mailPath = config('email-management.view_dir') . '/emails/' . $folderName;

        $mailViewPath = $mailPath . "/{$emailViewFileName}";

        if (!File::exists($mailPath)) {
            File::makeDirectory($mailPath, 0755, true);
        }

        if (File::exists($mailViewPath)) {
            $this->error("Mail View file already exists: {$mailViewPath}");

            return;
        }

        File::put($mailViewPath, $emailHandlerStub);

        $this->info("Created MailView file: {$mailViewPath}");
    }
}
