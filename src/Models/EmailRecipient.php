<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EmailRecipient extends Model
{
    protected $fillable = ['email_id', 'email', 'type'];

    public function email()
    {
        return $this->belongsTo(EmailLog::class);
    }
}
