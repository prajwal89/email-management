<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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

        // email has left from our app
        'sent_at',

        // 'resent_at',
        // 'accepted_at',
        // 'delivered_at',

        // Tracked from tracking pixel 
        'last_opened_at',

        // Last email visit time
        'last_clicked_at',
        'complained_at',

        'in_reply_to',
        'replied_at',

        // Recoverable bounce type (we can schedule this for later)
        'soft_bounced_at',

        // Non recoverable bounce type we have to remove this from our mailing list
        'hard_bounced_at',

        // user has unsubscribed b.c. of this email
        'unsubscribed_at',
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
            'reply_at' => 'datetime',
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
     * This can be User, NewsletterEmails, Cold Email Model
     */
    public function receivable(): MorphTo
    {
        return $this->morphTo();
    }

    #[Scope]
    public function sent(Builder $query)
    {
        $query->whereNotNull('sent_at');
    }

    #[Scope]
    public function opened(Builder $query): void
    {
        $query->whereNotNull('last_opened_at');
    }

    #[Scope]
    public function clicked(Builder $query): void
    {
        $query->whereNotNull('last_clicked_at');
    }

    #[Scope]
    public function replied(Builder $query): void
    {
        $query->whereNotNull('replied_at');
    }

    #[Scope]
    public function complained(Builder $query): void
    {
        $query->whereNotNull('complained_at');
    }

    #[Scope]
    public function softBounced(Builder $query): void
    {
        $query->whereNotNull('soft_bounced_at');
    }

    #[Scope]
    public function hardBounced(Builder $query): void
    {
        $query->whereNotNull('hard_bounced_at');
    }

    #[Scope]
    public function bounced(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereNotNull('soft_bounced_at')
                ->orWhereNotNull('hard_bounced_at');
        });
    }

    #[Scope]
    public function unsubscribed(Builder $query): void
    {
        $query->whereNotNull('unsubscribed_at');
    }

    // #[Scope]
    // public function engaged(Builder $query): void
    // {
    //     $query->where(function ($q) {
    //         $q->whereNotNull('last_opened_at')
    //             ->orWhereNotNull('last_clicked_at')
    //             ->orWhereNotNull('replied_at');
    //     });
    // }

    // #[Scope]
    // public function successful(Builder $query): void
    // {
    //     $query->whereNotNull('sent_at')
    //         ->whereNull('soft_bounced_at')
    //         ->whereNull('hard_bounced_at');
    // }

    // #[Scope]
    // public function failed(Builder $query): void
    // {
    //     $query->where(function ($q) {
    //         $q->whereNotNull('soft_bounced_at')
    //             ->orWhereNotNull('hard_bounced_at');
    //     });
    // }

    // #[Scope]
    // public function active(Builder $query): void
    // {
    //     $query->whereNull('unsubscribed_at')
    //         ->whereNull('hard_bounced_at');
    // }

    // #[Scope]
    // public function inactive(Builder $query): void
    // {
    //     $query->where(function ($q) {
    //         $q->whereNotNull('unsubscribed_at')
    //             ->orWhereNotNull('hard_bounced_at');
    //     });
    // }


    /**
     * Get the status of the email
     */
    public function getStatus(): string
    {
        // Check for bounces first (most critical)
        if ($this->hard_bounced_at) {
            return 'hard_bounced';
        }

        if ($this->soft_bounced_at) {
            return 'soft_bounced';
        }

        // Check for unsubscribe
        if ($this->unsubscribed_at) {
            return 'unsubscribed';
        }

        // Check for complaints
        if ($this->complained_at) {
            return 'complained';
        }

        // Check for engagement (in order of value)
        if ($this->replied_at) {
            return 'replied';
        }

        if ($this->last_clicked_at) {
            return 'clicked';
        }

        if ($this->last_opened_at) {
            return 'opened';
        }

        // Check if sent
        if ($this->sent_at) {
            return 'sent';
        }

        // Default status
        return 'pending';
    }
}
