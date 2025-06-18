<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\EmailView;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailVariant;

class EmailEventEmailView
{

    public function __construct(
        public string|Model $forModel,
        public array $modelAttributes,
        public ?string $sendableSlug,
        public ?string $variantSlug,
    ) {
        //
    }


    public function generateFile()
    {
        $stubPath = match ($this->modelAttributes['content_type']) {
            'markdown' => __DIR__ . '/../../../../stubs/email-views/email-markdown-view.stub',
            'html' => __DIR__ . '/../../../../stubs/email-views/email-html-view.stub',
            'text' => __DIR__ . '/../../../../stubs/email-views/email-text-view.stub',
        };

        $emailHandlerStub = str(File::get($stubPath))
            ->replace('{name}', trim($this->modelAttributes['name']));

        $emailFilePath = EmailVariant::getEmailViewFilePath(
            sendable: $this->forModel,
            variantSlug: $this->variantSlug,
            sendableSlug: $this->sendableSlug
        );

        $folderName = dirname($emailFilePath);

        if (!File::exists($folderName)) {
            File::makeDirectory($folderName, 0755, true);
        }

        if (File::exists($emailFilePath)) {
            throw new Exception("Mail View file already exists: {$emailFilePath}");

            return;
        }

        File::put($emailFilePath, $emailHandlerStub);

        return $emailFilePath;
    }
}
