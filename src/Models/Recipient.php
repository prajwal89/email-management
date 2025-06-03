<?php

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $table = 'em_recipients';

    protected $fillable = [
        'message_id',
        'email',
        'type',
    ];

    /**
     * Get the email log this recipient belongs to.
     */
    public function emailLog()
    {
        return $this->belongsTo(EmailLog::class, 'message_id', 'message_id');
    }
}
