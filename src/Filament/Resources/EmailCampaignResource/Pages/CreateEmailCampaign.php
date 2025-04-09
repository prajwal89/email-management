<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Prajwal89\EmailManagement\Filament\Resources\EmailCampaignResource;

class CreateEmailCampaign extends CreateRecord
{
    protected static string $resource = EmailCampaignResource::class;
}
