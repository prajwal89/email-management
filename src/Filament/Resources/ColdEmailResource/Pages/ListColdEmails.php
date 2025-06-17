<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\ColdEmailResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Prajwal89\EmailManagement\Filament\BaseTrendChartWidget;
use Prajwal89\EmailManagement\Filament\Resources\ColdEmailResource;
use Prajwal89\EmailManagement\Models\ColdEmail;

class ListColdEmails extends ListRecords
{
    protected static string $resource = ColdEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // protected function getFooterWidgets(): array
    // {
    //     return [
    //         BaseTrendChartWidget::make(['modelFqn' => ColdEmail::class]),
    //     ];
    // }
}
