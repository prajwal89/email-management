<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Prajwal89\EmailManagement\Traits\HasEmailLogs;

class NewsletterEmail extends Model implements EmailReceivable
{
    use HasEmailLogs, SoftDeletes, HasFactory;

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

    #[Override]
    #[Scope]
    public function subscribedToEmails(Builder $query): Builder
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
