<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\SentEmailResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Prajwal89\EmailManagement\Filament\Resources\SentEmailResource;

class EditSentEmail extends EditRecord
{
    protected static string $resource = SentEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
