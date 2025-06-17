<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\Seeders;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;

class EmailCampaignSeeder
{
    public function __construct(
        public string|Model $forModel,
        public array $modelAttributes
    ) {
        //
    }

    public function generateSeederFile()
    {
        $slug = str($this->modelAttributes['name'])->slug();

        if (EmailCampaign::query()->where('slug', $slug)->exists()) {
            throw new Exception('Email Campaign Name is Taken');
        }

        $seederClassName = $slug->studly() . 'Seeder';

        $fileContents = str(File::get(__DIR__ . '/../../../../stubs/seeders/sendable-seeder.stub'))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $this->modelAttributes['description'])
            ->replace('{content_type}', $this->modelAttributes['content_type'])
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns')
            ->replace('{seeder_class_name}', $seederClassName);

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailCampaigns';

        $filePath = $seederPath . "/{$seederFileName}";

        if (!File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        if (File::exists($filePath)) {
            throw new Exception("Seeder file is already available: {$filePath}");

            return;
        }

        File::put($filePath, $fileContents);

        return $filePath;
    }

    public function generateDeleteSeederFile()
    {
        $slug = str($this->forModel->slug);

        $seederClassName = $slug->studly() . 'DeleteSeeder';

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailCampaigns';

        $filePath = $seederPath . "/{$seederFileName}";

        $fileContents = str(File::get(__DIR__ . '/../../../../stubs/sendable-seeder-delete.stub'))
            ->replace('{slug}', $slug)
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{namespace_path}', 'EmailCampaigns')
            ->replace('{seeder_class_name}', $seederClassName);

        $seederFileName = "$seederClassName.php";

        if (!File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        if (File::exists($filePath)) {
            throw new Exception("Delete Seeder file is already available: {$filePath}");

            return;
        }

        File::put($filePath, $fileContents);

        return $filePath;
    }
}
