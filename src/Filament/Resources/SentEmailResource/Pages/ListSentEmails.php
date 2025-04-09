<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Widgets\SentEmailsTrendWidget;

class ListSentEmails extends ListRecords
{
    protected static string $resource = SentEmailResource::class;

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
