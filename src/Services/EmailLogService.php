<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Models\EmailLog;

class EmailLogService
{
    // we can invoke events from here
    public static function update(EmailLog $emailLog, array $attributes)
    {
        $emailLog->update($attributes);

        return true;
    }
}
