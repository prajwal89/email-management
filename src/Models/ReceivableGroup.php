<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\CampaignRunner;
use Sushi\Sushi;

/**
 * @property int $id
 * @property string $classname
 * @property string $FQN
 * @property int $total
 * @property string $description
 */
class ReceivableGroup extends Model
{
    use Sushi;

    public function getRows(): array
    {
        return CampaignRunner::allGroupsData()->toArray();
    }
}
