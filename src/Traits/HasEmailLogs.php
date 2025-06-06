<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Prajwal89\EmailManagement\Models\EmailLog;

trait HasEmailLogs
{
    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'receivable');
    }
}
