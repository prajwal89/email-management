<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament;

use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class SharedActions
{
    public static function createEmailVariant()
    {
        return Action::make('create_variant')
            ->icon('heroicon-o-plus')
            ->outlined()
            ->color('success')
            ->label('Email Variant')
            ->modalHeading('Instructions for Creating an Email Variant')
            ->modalContent(function ($record): Htmlable {
                $type = str(get_class($record))->afterLast('\\')->toString();

                return new HtmlString(
                    "php artisan em:create-email-variant --sendable_type=$type --sendable_slug={$record->slug}"
                );
            });
    }

    public static function createFollowUp()
    {
        return Action::make('create_follow_up')
            ->icon('heroicon-o-plus')
            ->outlined()
            ->color('info')
            ->label('Create Follow Up')
            ->modalHeading('Instructions for creating a follow up email')
            ->modalContent(function ($record): Htmlable {
                $type = addslashes(get_class($record));

                return new HtmlString(
                    "php artisan em:create-follow-up --type=$type --slug={$record->slug}"
                );
            });
    }
}
