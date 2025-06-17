<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\Seeders;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class EmailVariantSeeder
{
    public function __construct(
        public string|Model $forModel,
        public array $modelAttributes,
        public string $sendableType,
        public string $sendableSlug,
    ) {
        //
    }

    public function generateFile()
    {
        $slug = str($this->modelAttributes['name'])->slug();

        $seederClassName = str($this->sendableSlug)->studly() . $slug->studly() . 'Seeder';

        $seederFilePath =  __DIR__ . '/../../../../stubs/seeders/email-variant-seeder.stub';

        $seederStub = str(File::get($seederFilePath))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{exposure_percentage}', $this->modelAttributes['exposure_percentage'])
            ->replace('{content_type}', $this->modelAttributes['content_type'])
            ->replace('{seeder_class_name}', $seederClassName)
            ->replace('{sendable_type}', str($this->sendableType)->afterLast('\\'))
            ->replace('{sendable_slug}', $this->sendableSlug);

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailVariants';

        $filePath = $seederPath . "/{$seederFileName}";

        if (!File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        if (File::exists($filePath)) {
            throw new Exception("Seeder file already exists: {$filePath}");

            return;
        }

        File::put($filePath, $seederStub);

        return $seederPath;
    }

    public function generateDeleteSeederFile()
    {
        $slug = str($this->forModel->slug);

        $seederClassName = $slug->studly() . 'DeleteSeeder';

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailEvents';

        $filePath = $seederPath . "/{$seederFileName}";

        $seederFilePath =  __DIR__ . '/../../../../stubs/seeders/sendable-delete-seeder.stub';

        $fileContents = str(File::get($seederFilePath))
            ->replace('{slug}', $slug)
            ->replace('{sendable_model_name}', 'EmailEvent')
            ->replace('{namespace_path}', 'EmailEvents')
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
