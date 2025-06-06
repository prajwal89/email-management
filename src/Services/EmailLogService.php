<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Prajwal89\EmailManagement\Models\EmailEvent;
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
