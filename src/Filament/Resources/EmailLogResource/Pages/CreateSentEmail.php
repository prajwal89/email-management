<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailLogResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailLogResource;

class CreateSentEmail extends CreateRecord
{
    protected static string $resource = EmailLogResource::class;
}
