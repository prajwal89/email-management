<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Services\FileManagers\EmailView\EmailCampaignEmailView;
use Prajwal89\EmailManagement\Services\FileManagers\EmailView\EmailEventEmailView;

class EmailViewFileManager
{
    public array $modelAttributes = [];

    public ?string $sendableType = null;

    public ?string $sendableSlug = null;

    public function __construct(
        public string|Model $forModel
    ) {
        //
    }

    public function setAttributes(array $attributes)
    {
        $this->modelAttributes = $attributes;

        return $this;
    }

    public function setSendableType(string $sendableType)
    {
        $this->sendableType = $sendableType;

        return $this;
    }

    public function setSendableSlug(string $sendableSlug)
    {
        $this->sendableSlug = $sendableSlug;

        return $this;
    }

    public function generateFile()
    {
        return $this->resolveGenerator()->generateFile();
    }

    public function resolveGenerator()
    {
        return match (is_string(($this->forModel)) ? $this->forModel : get_class($this->forModel)) {
            EmailEvent::class => new EmailEventEmailView(
                $this->forModel,
                $this->modelAttributes,
                $this->sendableType,
                $this->sendableSlug,
            ),
            EmailCampaign::class => new EmailCampaignEmailView(
                $this->forModel,
                $this->modelAttributes,
                $this->sendableSlug,
                $this->sendableSlug,
            ),
        };
    }
}
