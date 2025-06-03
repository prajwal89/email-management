<?php

namespace Prajwal89\EmailManagement\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Prajwal89\EmailManagement\Models\EmailLog;

trait HasEmailLogs
{
    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'receivable');
    }
}
