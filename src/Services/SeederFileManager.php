<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

/**
 * Generate for delete seeder files for 
 * EmailEvent,EmailCampaign,EmailVariant
 */
class SeederFileManager
{
    public array $modelAttributes;

    public function __construct(public $forModel)
    {
        // 
    }

    public function setAttributes(array $attributes)
    {
        $this->modelAttributes = $attributes;
        return $this;
    }

    public function generateFile()
    {
        // we can create separate files
        return match ($this->forModel) {
            EmailEvent::class => $this->generateForEmailEvent(),
        };
    }

    public function generateDeleteRecordFile()
    {
        // we can create separate files
        return match ($this->forModel) {
            EmailEvent::class => $this->generateForEmailEventDeleteFile(),
        };
    }

    public function generateForEmailEvent()
    {
        $slug = str($this->modelAttributes['name'])->slug();

        if (EmailEvent::query()->where('slug', $slug)->exists()) {
            throw new Exception('Email Event Name is Taken');
        }

        $seederClassName = $slug->studly() . 'Seeder';

        $fileContents = str(File::get(__DIR__ . '/../../stubs/sendable-seeder.stub'))
            ->replace('{name}', $this->modelAttributes['name'])
            ->replace('{slug}', $slug)
            ->replace('{description}', $this->modelAttributes['description'])
            ->replace('{sendable_model_name}', 'EmailEvent')
            ->replace('{namespace_path}', 'EmailEvents')
            ->replace('{seeder_class_name}', $seederClassName);

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailEvents';

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

    public function generateForEmailEventDeleteFile()
    {
        $slug = str($this->modelAttributes['name'])->slug();

        $seederClassName = $slug->studly() . 'DeleteSeeder';

        $seederFileName = "$seederClassName.php";

        $seederPath = config('email-management.seeders_dir') . '/EmailEvents';

        $filePath = $seederPath . "/{$seederFileName}";

        if (!File::exists($filePath)) {
            return;
        }

        File::delete($filePath);

        return $filePath;
    }
}
