<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;

class NewsletterEmail extends Model implements EmailReceivable
{
    use SoftDeletes;

    protected $table = 'em_newsletter_emails';

    protected $fillable = [
        'email',
        'email_verified_at',
        'unsubscribed_at',
    ];

    public function casts(): array
    {
        return [
            'is_subscribed_for_emails' => 'boolean',
            'email_verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function getName(): string
    {
        return str($this->email)->before('@')->toString();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function scopeSubscribedToEmails(Builder $query): Builder
    {
        return $query
            ->whereNotNull('email_verified_at')
            ->whereNull('unsubscribed_at');
    }

    public function isSubscribedToEmails(): bool
    {
        return $this->email_verified_at !== null && $this->unsubscribed_at === null;
    }

    public function unsubscribeFromEmails(): bool
    {
        return $this->update(['unsubscribed_at' => now()]);
    }
}
