<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailVisitResource;

class EditEmailVisit extends EditRecord
{
    protected static string $resource = EmailVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
