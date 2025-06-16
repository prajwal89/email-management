<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\EmailHandlerFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class CreateEmailCampaignCommand extends Command
{
    protected $signature = 'em:create-email-campaign';

    protected $description = 'Command description';

    public function handle(): void
    {
        $data = [
            'name' => text(
                label: 'Campaign Name',
                hint: 'Keep it short. Do not include "Email" as suffix',
                required: true,
                validate: ['name' => 'required|max:40']
            ),
            'description' => textarea(
                label: 'Enter Campaign description',
                required: false
            ),
        ];

        $slug = str($data['name'])->slug();

        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        if (EmailCampaign::query()->where('slug', $slug)->exists()) {
            throw new Exception('Duplicate EmailCampaign name');
        }

        $this->createSeederFile($data);
        $this->createDefaultEmailVariantSeederFile(EmailCampaign::class, $slug->toString());

        $this->createEmailHandlerClassFile($data);
        $this->createEmailClass($data);
        $this->createEmailView($data);

        Artisan::call('em:seed-db');

        $this->info('Run: "php artisan em:seed-db" to seed the Email Event');
        $this->info('Implement email and Check if rending is correctly in filament panel');
        $this->info('Implement Handler Email Class');
        $this->info('Test sample email by sending to admin');
        $this->info("Use in App by: (new $emailHandlerClassName)->send()");
    }

    public function createSeederFile(array $data): void
    {
        $filePath = (new SeederFileManager(EmailCampaign::class))
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
        $emailHandlerFile = (new EmailHandlerFileManager(EmailCampaign::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Email Handler: {$emailHandlerFile}");
    }

    /**
     * laravel's default mailable class
     */
    public function createEmailClass(array $data): void
    {
        $slug = str($data['name'])->slug();

        $emailClassName = $slug->studly() . 'Email';

        $emailViewName = $slug . '-email';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../stubs/email-class.stub'))
            ->replace('{sendable_class_name}', 'EmailCampaigns') // aka folder name
            ->replace('{email_class_name}', $emailClassName)
            ->replace('{sendable_folder_name}', 'email-campaigns') // folder for email views
            ->replace('{email_subject}', $data['name'])
            ->replace('{email_view_file_name}', $emailViewName);

        $mailPath = config('email-management.mail_classes_path') . '/EmailCampaigns';
        $mailClassPath = $mailPath . "/{$emailClassName}.php";

        if (!File::exists($mailPath)) {
            File::makeDirectory($mailPath, 0755, true);
        }

        if (File::exists($mailClassPath)) {
            $this->error("Mail Class file already exists: {$mailClassPath}");

            return;
        }

        File::put($mailClassPath, $emailHandlerStub);

        $this->info("Created MailClass file: {$emailClassName}.php");
    }

    /**
     * markdown view for email
     */
    public function createEmailView(array $data): void
    {
        $slug = str($data['name'])->slug();

        $emailViewFileName = $slug . '-email.blade.php';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../stubs/email-markdown-view.stub'))
            ->replace('{name}', $data['name']);

        $mailPath = config('email-management.view_dir') . '/emails/email-campaigns';

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
