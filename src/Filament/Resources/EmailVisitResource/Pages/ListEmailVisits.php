<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Widgets\EmailVisitsTrendWidget;

class ListEmailVisits extends ListRecords
{
    protected static string $resource = EmailVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            EmailVisitsTrendWidget::class,
        ];
    }
}
