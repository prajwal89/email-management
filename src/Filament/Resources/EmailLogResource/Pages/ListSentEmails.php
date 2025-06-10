<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Widgets\SentEmailsTrendWidget;

class ListSentEmails extends ListRecords
{
    protected static string $resource = EmailLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            SentEmailsTrendWidget::class,
        ];
    }
}
