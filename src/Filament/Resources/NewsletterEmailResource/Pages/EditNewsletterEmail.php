<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource;

class EditNewsletterEmail extends EditRecord
{
    protected static string $resource = NewsletterEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
