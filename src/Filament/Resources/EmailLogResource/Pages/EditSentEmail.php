<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;

class EditSentEmail extends EditRecord
{
    protected static string $resource = EmailLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
