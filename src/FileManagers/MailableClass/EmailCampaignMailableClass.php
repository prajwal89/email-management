<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\FileManagers\MailableClass;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailCampaign;

class EmailCampaignMailableClass
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

        $emailClassName = EmailCampaign::getMailableClassName($slug->toString());

        $emailViewName = $slug . '-email';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../../../stubs/email-class.stub'))
            ->replace('{sendable_class_name}', 'EmailCampaigns') // aka folder name
            ->replace('{email_class_name}', $emailClassName)
            ->replace('{sendable_folder_name}', 'email-campaigns') // folder for email views
            ->replace('{email_subject}', $this->modelAttributes['name'])
            ->replace('{email_view_file_name}', $emailViewName);

        $mailableClassPath = EmailCampaign::getMailableClassPath($slug->toString());

        $mailPath = dirname($mailableClassPath);

        if (!File::exists($mailPath)) {
            File::makeDirectory($mailPath, 0755, true);
        }

        if (File::exists($mailableClassPath)) {
            throw new Exception("Mail Class file already exists: {$mailableClassPath}");
        }

        File::put($mailableClassPath, $emailHandlerStub);

        return $mailableClassPath;
    }
}
