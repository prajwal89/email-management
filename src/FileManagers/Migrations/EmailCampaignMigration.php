<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\FileManagers\Migrations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class EmailCampaignMigration
{
    public function __construct(
        public string|Model $forModel,
        public array $modelAttributes
    ) {
        //
    }

    public function generateFile()
    {
        $slug = str($this->modelAttributes['name'])->slug();

        $stubPath = __DIR__ . '/../../../stubs/migrations/email-campaign-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $this->modelAttributes['description'])
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns');

        $migrationFilePath = EmailCampaign::getMigrationFilePath($slug->toString(), 'seed');

        $directory = dirname($migrationFilePath);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($migrationFilePath, $fileContents);

        return $migrationFilePath;
    }

    public function generateDeleteSeederFile()
    {
        $slug = $this->forModel->slug;

        $stubPath = __DIR__ . '/../../../stubs/migrations/sendable-delete-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{slug}', $slug)
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns');

        $migrationFilePath = EmailCampaign::getMigrationFilePath($slug, 'deseed');

        $folder = dirname($migrationFilePath);

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        File::put($migrationFilePath, $fileContents);

        return $migrationFilePath;
    }
}
