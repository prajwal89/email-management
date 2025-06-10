<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;

class EmailVariantService
{
    public static function store(
        EmailEvent|EmailCampaign $eventable,
        array $attributes
    ): EmailVariant {
        return $eventable->emailVariants()->create($attributes);
    }

    public static function firstOrCreate(
        EmailEvent|EmailCampaign $eventable,
        array $find,
        array $attributes,
    ): EmailVariant {
        $emailVariant = EmailVariant::query()->where($find)->first();

        if ($emailVariant) {
            return $emailVariant;
        }

        return self::store($eventable, array_merge($find, $attributes));
    }

    public static function createDefaultVariant(EmailEvent|EmailCampaign $eventable): EmailVariant
    {
        $defaultAttributes = [
            'name' => 'Default',
            'slug' => 'default',
            'exposure_percentage' => 50,
        ];

        return $eventable->emailVariants()->create($defaultAttributes);
    }
}
