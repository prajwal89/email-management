<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\MailableClass\EmailCampaignMailableClass;
use Prajwal89\EmailManagement\Services\FileManagers\MailableClass\EmailEventMailableClass;
use Prajwal89\EmailManagement\Services\FileManagers\Seeders\EmailCampaignSeeder;
use Prajwal89\EmailManagement\Services\FileManagers\Seeders\EmailEventSeeder;
use Prajwal89\EmailManagement\Services\FileManagers\Seeders\EmailVariantSeeder;


class MailableClassFileManager
{
    public array $modelAttributes = [];

    public function __construct(public string|Model $forModel)
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
        return $this->resolveGenerator()->generateFile();
    }

    public function resolveGenerator()
    {
        return match (is_string(($this->forModel)) ? $this->forModel : get_class($this->forModel)) {
            EmailEvent::class => new EmailEventMailableClass(
                $this->forModel,
                $this->modelAttributes
            ),
            EmailCampaign::class => new EmailCampaignMailableClass(
                $this->forModel,
                $this->modelAttributes
            ),
        };
    }
}
