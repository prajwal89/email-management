<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\FileManagers;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\FileManagers\Migrations\EmailCampaignMigration;
use Prajwal89\EmailManagement\FileManagers\Migrations\EmailEventMigration;
use Prajwal89\EmailManagement\FileManagers\Migrations\EmailVariantMigration;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;

/**
 * Generate for delete seeder files for
 * EmailEvent,EmailCampaign,EmailVariant
 *
 * Seeder files are in pair for creating and deleting the record
 * and if both pair is available for single record we can safely remove both seeder files
 */
class MigrationFileManager
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
            EmailEvent::class => new EmailEventMigration(
                $this->forModel,
                $this->modelAttributes
            ),
            EmailCampaign::class => new EmailCampaignMigration(
                $this->forModel,
                $this->modelAttributes
            ),
            EmailVariant::class => new EmailVariantMigration(
                $this->forModel,
                $this->modelAttributes,
                $this->sendableType,
                $this->sendableSlug,
            ),
        };
    }
}
