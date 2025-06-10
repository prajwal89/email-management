<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;

use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class CreateEmailCampaignCommand extends Command
{
    protected $signature = 'email-management:create-email-campaign';

    protected $description = 'Command description';

    public function handle(): void
    {
        $data = [
            'name' => text(
                label: 'Enter campaign name',
                required: true,
                validate: ['name' => 'required|max:40']
            ),
            'description' => textarea(
                label: 'Enter campaign description',
                required: false
            ),
        ];

        $slug = str($data['name'])->slug();

        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        if (EmailEvent::query()->where('slug', $slug)->exists()) {
            throw new Exception('Duplicate EmailEvent name');
        }

        // todo what if some steps fail
        $this->createSeederFile($data);
        $this->createEmailHandlerClassFile($data);
        $this->createEmailClass($data);
        $this->createEmailView($data);

        $this->info('Run: "php artisan email-management:seed-db" to seed the Email Event');
        $this->info('Implement email and Check if rending is correctly in filament panel');
        $this->info('Implement Handler Email Class');
        $this->info('Test sample email by sending to admin');
        $this->info("Use in App by: (new $emailHandlerClassName)->send()");
    }

    public function createSeederFile(array $data): void
    {
        $slug = str($data['name'])->slug();

        // email to be created
        $seederClassName = $slug->studly() . 'Seeder';

        // migration
        $seederStub = str(File::get(__DIR__ . '/../../stubs/email-campaign-seeder.stub'))
            ->replace('{name}', $data['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $data['description'])
            ->replace('{seeder_class_name}', $seederClassName);

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailEvents';
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

    public function createEmailHandlerClassFile(array $data): void
    {
        $slug = str($data['name'])->slug();

        // email to be created
        $emailClassName = $slug->studly() . 'Email';

        // handler class
        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../stubs/email-handler.stub'))
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{sendable_class_name}', 'EmailCampaigns') // Folder name
            ->replace('{email_class}', $emailClassName)
            ->replace('{email_handler_class_name}', $emailHandlerClassName)
            ->replace('{email_class_name_space}', "App\EmailManagement\Emails\EmailCampaigns\\" . $emailClassName)
            ->replace('{event_slug}', $slug);

        $handlerPath = config('email-management.email_handlers_dir') . '/EmailCampaigns';
        $handlerFilePath = $handlerPath . "/{$emailHandlerClassName}.php";

        if (File::exists($handlerFilePath)) {
            $this->error("MailHandler file already exists: {$handlerFilePath}");

            return;
        }

        if (!File::exists($handlerPath)) {
            File::makeDirectory($handlerPath, 0755, true);
        }

        File::put($handlerFilePath, $emailHandlerStub);

        $this->info("Created MailHandler file: {$emailHandlerClassName}.php");
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
