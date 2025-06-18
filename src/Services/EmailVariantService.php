<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailVariant;
use Prajwal89\EmailManagement\Services\FileManagers\SeederFileManager;

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
        $emailVariant = $sendable->emailVariants()->where($find)->first();

        if ($emailVariant) {
            return $emailVariant;
        }

        return self::store($sendable, array_merge($find, $attributes));
    }

    public static function createDefaultVariant(EmailSendable $sendable): EmailVariant
    {
        $defaultAttributes = (new EmailVariant)->getDefaultAttributes();

        return $sendable->emailVariants()->firstOrCreate($defaultAttributes);
    }

    public static function destroy(EmailVariant $emailVariant)
    {
        if (!app()->isLocal()) {
            throw new Exception('Email Variants Can Be Deleted from Local Environment Only');
        }

        $emailViewFilePath = EmailVariant::getEmailViewFilePath(
            $emailVariant->sendable,
            $emailVariant->slug,
        );

        $exitCode = Artisan::call('em:seed-db');

        File::delete($emailViewFilePath);

        (new SeederFileManager($emailVariant->sendable))->generateDeleteSeederFile();
        (new SeederFileManager($emailVariant))->generateDeleteSeederFile();

        // effectively deleted the email variant record
        $exitCode = Artisan::call('em:seed-db');

        if ($exitCode !== 0) {
            throw new Exception('command php artisan em:seed-db Failed ' . $exitCode);
        }

        return true;
    }
}
