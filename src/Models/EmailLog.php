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
        'from',
        'mailer',
        'transport',
        'subject',
        'receivable_id',
        'receivable_type',
        'eventable_id',
        'eventable_type',
        'context',
        'headers',
        'html',
        'text',
        'opens',
        'clicks',
        'sent_at', // email has left from our app
        'resent_at',
        'accepted_at',
        // 'delivered_at',
        'last_opened_at',
        'last_clicked_at',
        'complained_at',
        'soft_bounced_at',
        'hard_bounced_at',
        'unsubscribed_at', //user has unsubscribed bc of this email
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'headers' => 'array',
            'sent_at' => 'datetime',
            'resent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'last_clicked_at' => 'datetime',
            'complained_at' => 'datetime',
            'soft_bounced_at' => 'datetime',
            'hard_bounced_at' => 'datetime',
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

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class, 'message_id', 'message_id');
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
