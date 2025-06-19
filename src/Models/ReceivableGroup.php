<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Services\CampaignManager;
use Sushi\Sushi;

class ReceivableGroup extends Model
{
    use Sushi;

    public function getRows(): array
    {
        return CampaignManager::allGroupsData()->toArray();
    }
}
