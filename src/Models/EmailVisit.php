<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVisit extends Model
{
    protected $table = 'em_email_visits';

    protected $fillable = [
        'path',
        'session_id',
        'ip',
        'message_id',
    ];

    public function emailLogs(): BelongsTo
    {
        return $this->belongsTo(EmailLog::class, 'message_id', 'message_id');
    }
}
