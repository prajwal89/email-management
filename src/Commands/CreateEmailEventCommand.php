<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\EmailHandlerFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\EmailViewFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\MailableClassFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class CreateEmailEventCommand extends Command
{
    protected $signature = 'em:create-email-event';

    protected $description = 'Command description';

    // todo setup classnames slug as public properties as we are using it multiple times

    public function handle(): void
    {
        $data = [
            'name' => text(
                label: 'Event Name',
                hint: 'Keep it short. Do not include "Email" as suffix',
                required: true,
                validate: ['name' => 'required|max:40']
            ),
            'description' => textarea(
                label: 'Enter Event description',
                required: false
            ),
        ];

        $slug = str($data['name'])->slug();

        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        if (EmailEvent::query()->where('slug', $slug)->exists()) {
            throw new Exception('Duplicate EmailEvent name');
        }

        $this->createSeederFile($data);

        $this->createDefaultEmailVariantSeederFile(EmailEvent::class, $slug->toString());

        $this->createEmailHandlerClassFile($data);
        $this->createEmailClass($data);
        $this->createEmailView($data);

        // seed created email event
        Artisan::call('em:seed-db');

        $this->info('Implement email view and Check if rending is correctly in filament panel');
        $this->info('Implement Handler Email Class');
        $this->info('Test sample email by sending to admin');
        $this->info("Use in App by: (new $emailHandlerClassName)->send()");
    }

    public function createSeederFile(array $data): void
    {
        $filePath = (new SeederFileManager(EmailEvent::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Seeder file: {$filePath}");
    }

    public function createDefaultEmailVariantSeederFile(
        string $sendableType,
        string $sendableSlug
    ): void {
        $filePath = (new SeederFileManager(EmailVariant::class))
            ->setAttributes((new EmailVariant)->getDefaultAttributes())
            ->setSendableType($sendableType)
            ->setSendableSlug($sendableSlug)
            ->generateFile();

        $this->info("Created Email Variant Seeder file: {$filePath}");
    }

    public function createEmailHandlerClassFile(array $data): void
    {
        $emailHandlerFile = (new EmailHandlerFileManager(EmailEvent::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Email Handler: {$emailHandlerFile}");
    }

    /**
     * laravel's default mailable class
     */
    public function createEmailClass(array $data): void
    {
        $mailableClassFile = (new MailableClassFileManager(EmailEvent::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Mailable Class file: {$mailableClassFile}.php");
    }

    /**
     * markdown view for email
     */
    public function createEmailView(array $data): void
    {
        $viewFile = (new EmailViewFileManager(EmailEvent::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Mail View file: {$viewFile}.php");
    }
}
