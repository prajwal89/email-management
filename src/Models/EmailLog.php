<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Prajwal89\EmailManagement\Enums\EmailStatus;
use Prajwal89\EmailManagement\Enums\RecipientType;

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
        'sendable_id',
        'sendable_type',
        'context',
        'headers',
        'html',
        'text',
        'opens',
        'clicks',

        'email_variant_id',

        // email has left from our app
        'sent_at',

        // Tracked from tracking pixel
        'last_opened_at',

        // Last email visit time
        'last_clicked_at',
        'complained_at',

        // this email log is reply to the email log with following message id
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
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function emailVisits(): HasMany
    {
        return $this->hasMany(EmailVisit::class, 'message_id', 'message_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class, 'message_id', 'message_id');
    }

    public function to(): HasOne
    {
        return $this->hasOne(Recipient::class, 'message_id', 'message_id')->where('type', RecipientType::TO);
    }

    public function emailVariant(): BelongsTo
    {
        return $this->belongsTo(EmailVariant::class, 'email_variant_id', 'id');
    }

    /**
     * This can be EmailEvent, EmailCampaign Model
     */
    public function sendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * This can be User, NewsletterEmails, ColdEmail Model
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

    #[Scope]
    public function successful(Builder $query): void
    {
        $query->whereNotNull('sent_at')
            ->whereNull('soft_bounced_at')
            ->whereNull('hard_bounced_at');
    }

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
     * Get the email status
     */
    public function getStatus(): EmailStatus
    {
        return EmailStatus::fromEmailLog($this);
    }

    /**
     * Get the status as string (for backward compatibility)
     */
    public function getStatusString(): string
    {
        return $this->getStatus()->value;
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, EmailStatus $status)
    {
        return match ($status) {
            EmailStatus::HARD_BOUNCED => $query->whereNotNull('hard_bounced_at'),
            EmailStatus::SOFT_BOUNCED => $query->whereNotNull('soft_bounced_at')
                ->whereNull('hard_bounced_at'),
            EmailStatus::UNSUBSCRIBED => $query->whereNotNull('unsubscribed_at')
                ->whereNull('hard_bounced_at')
                ->whereNull('soft_bounced_at'),
            EmailStatus::COMPLAINED => $query->whereNotNull('complained_at')
                ->whereNull('hard_bounced_at')
                ->whereNull('soft_bounced_at')
                ->whereNull('unsubscribed_at'),
            EmailStatus::REPLIED => $query->whereNotNull('replied_at')
                ->whereNull('hard_bounced_at')
                ->whereNull('soft_bounced_at')
                ->whereNull('unsubscribed_at')
                ->whereNull('complained_at'),
            EmailStatus::CLICKED => $query->whereNotNull('last_clicked_at')
                ->whereNull('replied_at')
                ->whereNull('hard_bounced_at')
                ->whereNull('soft_bounced_at')
                ->whereNull('unsubscribed_at')
                ->whereNull('complained_at'),
            EmailStatus::OPENED => $query->whereNotNull('last_opened_at')
                ->whereNull('last_clicked_at')
                ->whereNull('replied_at')
                ->whereNull('hard_bounced_at')
                ->whereNull('soft_bounced_at')
                ->whereNull('unsubscribed_at')
                ->whereNull('complained_at'),
            EmailStatus::SENT => $query->whereNotNull('sent_at')
                ->whereNull('last_opened_at')
                ->whereNull('last_clicked_at')
                ->whereNull('replied_at')
                ->whereNull('hard_bounced_at')
                ->whereNull('soft_bounced_at')
                ->whereNull('unsubscribed_at')
                ->whereNull('complained_at'),
            EmailStatus::PENDING => $query->whereNull('sent_at'),
        };
    }
}
