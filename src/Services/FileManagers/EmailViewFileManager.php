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

    public function __construct(
        public string|Model $forModel,
        public string $sendableSlug,
        public string $variantSlug,
    ) {
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
            EmailEvent::class => new EmailEventEmailView(
                forModel: $this->forModel,
                modelAttributes: $this->modelAttributes,
                sendableSlug: $this->sendableSlug,
                variantSlug: $this->variantSlug
            ),
            EmailCampaign::class => new EmailCampaignEmailView(
                forModel: $this->forModel,
                modelAttributes: $this->modelAttributes,
                sendableSlug: $this->sendableSlug,
                variantSlug: $this->variantSlug
            ),
        };
    }
}
