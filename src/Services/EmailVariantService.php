<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\EmailVariant;

class EmailVariantService
{
    public static function store(
        EmailEvent | EmailCampaign $eventable,
        array $attributes
    ): EmailVariant {
        return $eventable->emailVariants()->create($attributes);
    }
}
