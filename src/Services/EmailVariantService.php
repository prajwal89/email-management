<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailCampaign;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\EmailVariant;

class EmailVariantService
{
    public static function store(
        EmailSendable $sendable,
        array $attributes
    ): EmailVariant {
        return $sendable->emailVariants()->create($attributes);
    }

    public static function firstOrCreate(
        EmailSendable $sendable,
        array $find,
        array $attributes,
    ): EmailVariant {
        $emailVariant = EmailVariant::query()->where($find)->first();

        if ($emailVariant) {
            return $emailVariant;
        }

        return self::store($sendable, array_merge($find, $attributes));
    }

    public static function createDefaultVariant(EmailSendable $sendable): EmailVariant
    {
        $defaultAttributes = [
            'name' => 'Default',
            'slug' => 'default',
            'exposure_percentage' => 50,
        ];

        return $sendable->emailVariants()->firstOrCreate($defaultAttributes);
    }
}
