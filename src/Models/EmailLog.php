<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends Model
{
    protected $table = 'em_email_logs';

    protected $fillable = [
        'message_id',
        'subject',
        'receivable_id',
        'receivable_type',
        'eventable_id',
        'eventable_type',
        'context',
        'headers',
        'sender_email',
        'recipient_email',
        'email_content',
        'opened_at',
        'clicked_at',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'context' => 'array',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    public function emailEvent(): BelongsTo
    {
        return $this->belongsTo(EmailEvent::class, 'email_event_id');
    }

    public function emailVisits(): HasMany
    {
        return $this->hasMany(EmailVisit::class, 'message_id', 'message_id');
    }


    /**
     * This can be EmailEvent, Campaign Model
     */
    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * This can be User, NewsletterEmails Model
     */
    public function receivable(): MorphTo
    {
        return $this->morphTo();
    }
}
