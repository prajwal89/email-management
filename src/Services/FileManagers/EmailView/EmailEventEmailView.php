<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\EmailView;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class EmailEventEmailView
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

        $emailViewFileName = $slug . '-email.blade.php';

        $emailHandlerStub = str(File::get(__DIR__ . '/../../../../stubs/email-markdown-view.stub'))
            ->replace('{name}', $this->modelAttributes['name']);

        $mailPath = config('email-management.view_dir') . '/emails/email-events';

        $mailViewPath = $mailPath . "/{$emailViewFileName}";

        if (!File::exists($mailPath)) {
            File::makeDirectory($mailPath, 0755, true);
        }

        if (File::exists($mailViewPath)) {
            throw new Exception("Mail View file already exists: {$mailViewPath}");

            return;
        }

        File::put($mailViewPath, $emailHandlerStub);

        return $mailViewPath;
    }
}
