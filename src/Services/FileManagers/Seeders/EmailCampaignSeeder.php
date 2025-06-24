<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\Seeders;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class EmailCampaignSeeder
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

        $stubPath = __DIR__ . '/../../../../stubs/migrations/sendable-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $this->modelAttributes['description'])
            // ->replace('{content_type}', $this->modelAttributes['content_type'])
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns')
            // ->replace('{seeder_class_name}', $seederClassName)
        ;


        $seederFilePath = EmailCampaign::getMigrationFilePath($slug->toString(), 'create');

        $directory = dirname($seederFilePath);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($seederFilePath)) {
            throw new Exception("Seeder file is already available: {$seederFilePath}");

            return;
        }

        File::put($seederFilePath, $fileContents);

        return $seederFilePath;
    }

    public function generateDeleteSeederFile()
    {
        $slug = str($this->forModel->slug);

        $seederClassName = EmailCampaign::getSeederFileClassName($slug->toString(), 'delete');

        $fileContents = str(File::get(__DIR__ . '/../../../../stubs/seeders/sendable-delete-seeder.stub'))
            ->replace('{slug}', $slug)
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns')
            ->replace('{seeder_class_name}', $seederClassName);

        $seederFilePath = EmailCampaign::getSeederFilePath($slug->toString(), 'delete');

        $folder = dirname($seederFilePath);

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        if (File::exists($seederFilePath)) {
            throw new Exception("Delete Seeder file is already available: {$seederFilePath}");

            return;
        }

        File::put($seederFilePath, $fileContents);

        return $seederFilePath;
    }
}
