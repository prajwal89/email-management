<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\FileManagers\Migrations;

use Illuminate\Support\Facades\File;
use LogicException;
use Prajwal89\EmailManagement\Models\EmailVariant;

class EmailVariantMigration
{
    public function __construct(
        public string|EmailVariant $forModel,
        public array $modelAttributes,
        public ?string $sendableType,
        public ?string $sendableSlug,
    ) {
        //
    }

    public function generateFile()
    {
        if (is_null($this->sendableSlug) || is_null($this->sendableType)) {
            throw new LogicException('Sendable Slug and Type Required');
        }

        $slug = str($this->modelAttributes['name'])->slug()->toString();

        $stubPath = __DIR__ . '/../../../stubs/migrations/email-variant-migration.stub';

        $seederStub = str(File::get($stubPath))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{exposure_percentage}', $this->modelAttributes['exposure_percentage'])
            ->replace('{content_type}', $this->modelAttributes['content_type'])
            ->replace('{sendable_type}', str($this->sendableType)->afterLast('\\'))
            ->replace('{sendable_slug}', $this->sendableSlug);

        $migrationFilePath = EmailVariant::getMigrationFilePath(
            sendableType: $this->sendableType,
            sendableSlug: $this->sendableSlug,
            variantSlug: $slug,
        );

        $filePath = dirname($migrationFilePath);

        if (!File::exists($filePath)) {
            File::makeDirectory($filePath, 0755, true);
        }

        File::put($migrationFilePath, $seederStub);

        return $migrationFilePath;
    }

    public function generateDeleteMigrationFile()
    {
        $slug = $this->forModel->slug;

        $stubPath = __DIR__ . '/../../../stubs/migrations/email-variant-delete-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{sendable_type}', get_class($this->forModel->sendable))
            ->replace('{sendable_slug}', $this->forModel->sendable->slug)
            ->replace('{slug}', $slug);

        $migrationFilePath = EmailVariant::getMigrationFilePath(
            sendableType: addslashes(get_class($this->forModel->sendable)),
            sendableSlug: $this->forModel->sendable->slug,
            variantSlug: $slug,
            type: 'deseed'
        );

        $folder = dirname($migrationFilePath);

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        File::put($migrationFilePath, $fileContents);

        return $migrationFilePath;
    }
}
