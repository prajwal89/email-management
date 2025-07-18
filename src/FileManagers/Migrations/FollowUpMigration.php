<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\FileManagers\Migrations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\FollowUp;

class FollowUpMigration
{
    public function __construct(
        // public string|Model $forModel,
        public array $modelAttributes,
        public EmailEvent $followupAbleEvent,
        public EmailSendable $followupAble,
    ) {
        //
    }

    public function generateFile()
    {
        $stubPath = __DIR__ . '/../../../stubs/migrations/followup-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{followup_email_event_slug}', $this->modelAttributes['followup_email_event_slug'])
            ->replace('{followupable_type}', $this->modelAttributes['followupable_type'])
            ->replace('{followupable_slug}', $this->modelAttributes['followupable_slug'])
            ->replace('{wait_for_days}', $this->modelAttributes['wait_for_days']);

        $migrationFilePath = FollowUp::getMigrationFilePath(
            $this->followupAbleEvent,
            $this->followupAble,
            'seed'
        );

        $directory = dirname($migrationFilePath);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($migrationFilePath, $fileContents);

        return $migrationFilePath;
    }

    public function generateDeleteMigrationFile()
    {
        $stubPath = __DIR__ . '/../../../stubs/migrations/followup-delete-migration.stub';

        $fileContents = str(File::get($stubPath))
            ->replace('{followup_email_event_slug}', $this->followupAbleEvent->slug)
            ->replace('{followupable_type}', get_class($this->followupAble))
            ->replace('{followupable_slug}', $this->followupAble->slug);

        $migrationFilePath = FollowUp::getMigrationFilePath(
            $this->followupAbleEvent,
            $this->followupAble,
            'deseed'
        );

        $folder = dirname($migrationFilePath);

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        File::put($migrationFilePath, $fileContents);

        return $migrationFilePath;
    }
}
