<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services\FileManagers;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\Seeders\EmailCampaignSeeder;
use Prajwal89\EmailManagement\Services\FileManagers\Seeders\EmailEventSeeder;
use Prajwal89\EmailManagement\Services\FileManagers\Seeders\EmailVariantSeeder;

/**
 * Generate for delete seeder files for
 * EmailEvent,EmailCampaign,EmailVariant
 *
 * Seeder files are in pair for creating and deleting the record
 * and if both pair is available for single record we can safely remove both seeder files
 */
class SeederFileManager
{
    public array $modelAttributes = [];

    public ?string $sendableType = null;

    public ?string $sendableSlug = null;

    public function __construct(public string|Model $forModel)
    {
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

    public function generateDeleteSeederFile()
    {
        return $this->resolveGenerator()->generateDeleteSeederFile();
    }

    public function resolveGenerator()
    {
        return match (is_string(($this->forModel)) ? $this->forModel : get_class($this->forModel)) {
            EmailEvent::class => new EmailEventSeeder(
                $this->forModel,
                $this->modelAttributes
            ),
            EmailCampaign::class => new EmailCampaignSeeder(
                $this->forModel,
                $this->modelAttributes
            ),
            EmailVariant::class => new EmailVariantSeeder(
                $this->forModel,
                $this->modelAttributes,
                $this->sendableType,
                $this->sendableSlug,
            ),
        };
    }
}
