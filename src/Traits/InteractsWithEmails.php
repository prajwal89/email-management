<?php

namespace Prajwal89\EmailManagement\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Prajwal89\EmailManagement\Models\EmailLog;

trait InteractsWithEmails
{
    public abstract function getName(): string;

    public abstract function getEmail(): string;

    #[Scope]
    public abstract function subscribedToEmails(Builder $query): Builder;

    public abstract function isSubscribedToEmails(): bool;

    public abstract function unsubscribeFromEmails(): bool;

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'receivable');
    }
}
