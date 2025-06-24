<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\Migrations;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;

class EmailEventMigration
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

        if (EmailEvent::query()->where('slug', $slug)->exists()) {
            throw new Exception('Email Event Name is Taken');
        }

        $stubPath = __DIR__ . '/../../../../stubs/migrations/email-event-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $this->modelAttributes['description'])
            ->replace('{is_followup_email}', $this->modelAttributes['is_followup_email'])
            ->replace('{sendable_model_name}', 'EmailEvent')
            ->replace('{namespace_path}', 'EmailEvents');

        $migrationFilePath = EmailEvent::getMigrationFilePath($slug->toString(), 'seed');

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

        $stubPath = __DIR__ . '/../../../../stubs/migrations/sendable-delete-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{slug}', $slug)
            ->replace('{sendable_model_name}', 'EmailEvent')
            ->replace('{namespace_path}', 'EmailEvents');

        $migrationFilePath = EmailEvent::getMigrationFilePath($slug, 'deseed');

        $folder = dirname($migrationFilePath);

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        File::put($migrationFilePath, $fileContents);

        return $migrationFilePath;
    }
}
