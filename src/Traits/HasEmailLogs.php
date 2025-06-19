<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Interfaces\EmailSendable;
use Prajwal89\EmailManagement\Models\EmailLog;

trait HasEmailLogs
{
    public function emailLogs(): MorphMany
    {
        if ($this instanceof EmailReceivable) {
            return $this->morphMany(EmailLog::class, 'receivable');
        }

        return $this->morphMany(EmailLog::class, 'sendable');
    }
}
