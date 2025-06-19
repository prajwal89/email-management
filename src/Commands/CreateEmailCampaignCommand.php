<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\EmailHandlerFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\EmailViewFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\MailableClassFileManager;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

/**
 * php artisan em:create-email-campaign
 */
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
            'content_type' => select(
                label: 'What is the content type for default variant?',
                options: [
                    'html' => 'HTML',
                    'markdown' => 'Markdown',
                    'text' => 'Plain Text',
                ],
                default: 'markdown',
                required: true,
                validate: fn(string $value) => in_array($value, ['html', 'markdown', 'text'], true)
                    ? null
                    : 'Invalid content type selected.'
            ),
            'once_per_receivable' => select(
                label: 'Send Email once per receivable',
                options: [
                    1 => 'Yes',
                    0 => 'No',
                ],
                default: 1,
                required: true,
            )
        ];

        $slug = str($data['name'])->slug();

        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        if (EmailCampaign::query()->where('slug', $slug)->exists()) {
            throw new Exception('Duplicate EmailCampaign name');
        }

        $this->createSeederFile($data);

        $this->createDefaultEmailVariantSeederFile($data, EmailCampaign::class, $slug->toString());

        $this->createEmailHandlerClassFile($data);

        $this->createEmailClass($data);

        $this->createEmailView(
            modelAttributes: $data,
            sendableSlug: $slug->toString()
        );

        Artisan::call('em:seed-db');

        $this->info("Use: (new $emailHandlerClassName())->send()");
    }

    public function createSeederFile(array $data): void
    {
        $filePath = (new SeederFileManager(EmailCampaign::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Seeder file: {$filePath}");
    }

    public function createDefaultEmailVariantSeederFile(
        array $data,
        string $sendableType,
        string $sendableSlug
    ): void {
        $filePath = (new SeederFileManager(EmailVariant::class))
            ->setAttributes(
                array_merge(
                    (new EmailVariant)->getDefaultAttributes(),
                    ['content_type' => $data['content_type']]
                )
            )->setSendableType($sendableType)
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
        $mailableClassFile = (new MailableClassFileManager(EmailCampaign::class))
            ->setAttributes($data)
            ->generateFile();

        $this->info("Created Mailable Class file: {$mailableClassFile}.php");
    }

    /**
     * ! technically this is for default email view
     */
    public function createEmailView(
        array $modelAttributes,
        string $sendableSlug,
        string $variantSlug = 'default',
    ): void {
        $viewFile = (new EmailViewFileManager(
            forModel: EmailCampaign::class,
            sendableSlug: $sendableSlug,
            variantSlug: $variantSlug
        ))
            ->setAttributes($modelAttributes)
            ->generateFile();

        $this->info("Created Mail View file: {$viewFile}.php");
    }
}
