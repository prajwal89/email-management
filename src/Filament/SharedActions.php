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
                return new HtmlString(
                    "php artisan em:create-email-variant --sendable_type=EmailEvent --sendable_slug={$record->slug}"
                );
            });
    }
}
