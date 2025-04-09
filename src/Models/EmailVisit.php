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
        'email_hash',
    ];

    public function sentEmail(): BelongsTo
    {
        return $this->belongsTo(SentEmail::class, 'email_hash', 'hash');
    }
}
