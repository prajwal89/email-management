<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers\EmailView;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class EmailCampaignEmailView
{
    public function __construct(
        public string|Model $forModel,
        public array $modelAttributes
    ) {
        //
    }

    public function generateFile() {}
}
