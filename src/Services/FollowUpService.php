<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\FileManagers\Migrations\FollowUpMigration;
use Prajwal89\EmailManagement\Models\FollowUp;

class FollowUpService
{
    public static function destroy(FollowUp $followUp)
    {
        (new FollowUpMigration(
            modelAttributes: [],
            followupAbleEvent: $followUp->followupEmailEvent,
            followupAble: $followUp->followupable
        ))
            ->generateDeleteSeederFile();

        return true;
    }
}
