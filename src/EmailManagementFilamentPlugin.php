<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Prajwal89\EmailManagement\Filament\Resources\ColdEmailResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource;

class EmailManagementFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'analytics';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                EmailEventResource::class,
                EmailCampaignResource::class,
                ColdEmailResource::class,
                NewsletterEmailResource::class,
                SentEmailResource::class,
                EmailVisitResource::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Emails')
                    ->icon('heroicon-o-envelope')
                    ->collapsed(),
            ])
            ->pages([
                // Settings::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
