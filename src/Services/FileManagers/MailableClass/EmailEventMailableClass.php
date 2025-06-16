<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\MailableClass;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;

class EmailEventMailableClass
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

        $emailClassName = $slug->studly() . 'Email';

        $emailViewName = $slug . '-email';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../../../stubs/email-class.stub'))
            ->replace('{sendable_class_name}', 'EmailEvents') // aka folder name
            ->replace('{email_class_name}', $emailClassName)
            ->replace('{sendable_folder_name}', 'email-events') // folder for email views
            ->replace('{email_subject}', $this->modelAttributes['name'])
            ->replace('{email_view_file_name}', $emailViewName);

        $mailPath = config('email-management.mail_classes_path') . '/EmailEvents';

        $mailClassPath = $mailPath . "/{$emailClassName}.php";

        if (!File::exists($mailPath)) {
            File::makeDirectory($mailPath, 0755, true);
        }

        if (File::exists($mailClassPath)) {
            throw new Exception("Mail Class file already exists: {$mailClassPath}");
        }

        File::put($mailClassPath, $emailHandlerStub);

        return $mailClassPath;
    }
}
