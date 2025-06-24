<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\Migrations;

use Exception;
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

        if (EmailCampaign::query()->where('slug', $slug)->exists()) {
            throw new Exception('Email Campaign Name is Taken');
        }

        $stubPath = __DIR__ . '/../../../../stubs/migrations/email-campaign-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $this->modelAttributes['description'])
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns');

        $seederFilePath = EmailCampaign::getMigrationFilePath($slug->toString(), 'seed');

        $directory = dirname($seederFilePath);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($seederFilePath, $fileContents);

        return $seederFilePath;
    }

    public function generateDeleteSeederFile()
    {
        $slug = $this->forModel->slug;

        $stubPath = __DIR__ . '/../../../../stubs/migrations/sendable-delete-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{slug}', $slug)
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns');

        $seederFilePath = EmailCampaign::getMigrationFilePath($slug, 'deseed');

        $folder = dirname($seederFilePath);

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        File::put($seederFilePath, $fileContents);

        return $seederFilePath;
    }
}
