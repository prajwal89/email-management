<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Prajwal89\EmailManagement\Enums\RecipientType;

class Recipient extends Model
{
    protected $table = 'em_recipients';

    protected $fillable = [
        'message_id',
        'email',
        'type',
    ];

    public function casts()
    {
        return [
            'type' => RecipientType::class,
        ];
    }

    /**
     * Get the email log this recipient belongs to.
     */
    public function emailLog()
    {
        return $this->belongsTo(EmailLog::class, 'message_id', 'message_id');
    }
}
