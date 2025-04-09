<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Prajwal89\EmailManagement\Filament\Resources\NewsletterEmailResource;

class CreateNewsletterEmail extends CreateRecord
{
    protected static string $resource = NewsletterEmailResource::class;
}
