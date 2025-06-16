<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\Handlers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;

class EmailCampaignHandler
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

        $emailHandlerClassName = str($slug)->studly() . 'EmailHandler';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../../../stubs/email-handler.stub'))
            ->replace('{sendable_model_name}', 'EmailCampaign')
            ->replace('{sendable_class_name}', 'EmailCampaigns') // Folder name
            ->replace('{mailable_class}', $emailClassName)
            ->replace('{email_handler_class_name}', $emailHandlerClassName)
            ->replace('{mailable_class_name_space}', "App\EmailManagement\Emails\EmailCampaigns\\" . $emailClassName)
            ->replace('{sendable_slug}', $slug);

        $handlerPath = config('email-management.email_handlers_dir') . '/EmailCampaigns';

        $handlerFilePath = $handlerPath . "/{$emailHandlerClassName}.php";

        if (File::exists($handlerFilePath)) {
            throw new Exception("MailHandler file already exists: {$handlerFilePath}");

            return;
        }

        if (!File::exists($handlerPath)) {
            File::makeDirectory($handlerPath, 0755, true);
        }

        File::put($handlerFilePath, $emailHandlerStub);

        return $handlerFilePath;
    }
}
