<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailEventResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailEventResource;

class CreateEmailEvent extends CreateRecord
{
    protected static string $resource = EmailEventResource::class;
}
