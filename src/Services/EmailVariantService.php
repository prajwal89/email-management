<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\FileManagers\MigrationFileManager;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
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
            throw new Exception('Email Variants Can Be Deleted from Local Environment Only. as it includes deleting files.');
        }

        $emailVariant->load('sendable');

        $emailViewFilePath = EmailVariant::getEmailViewFilePath(
            $emailVariant->sendable,
            $emailVariant->slug,
        );

        // delete sendable record
        // (new MigrationFileManager($emailVariant->sendable))->generateDeleteMigrationFile();

        // delete email variant
        (new MigrationFileManager($emailVariant))->generateDeleteMigrationFile();

        File::delete($emailViewFilePath);

        // effectively deleted the email variant record
        // ask for if to run migrations
        $exitCode = Artisan::call('migrate');

        if ($exitCode !== 0) {
            throw new Exception('command `php artisan migrate` Failed ' . $exitCode);
        }

        return true;
    }
}
