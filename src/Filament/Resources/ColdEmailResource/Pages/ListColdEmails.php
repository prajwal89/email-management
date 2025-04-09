<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\ColdEmailResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Prajwal89\EmailManagement\Filament\Resources\ColdEmailResource;

class ListColdEmails extends ListRecords
{
    protected static string $resource = ColdEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
