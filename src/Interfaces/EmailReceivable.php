<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface EmailReceivable
{
    public function getName(): string;

    public function getEmail(): string;

    // all receivables that are subscribed
    public function scopeSubscribedToEmails(Builder $query): Builder;

    public function isSubscribedToEmails(): bool;

    public function unsubscribeFromEmails(): bool;
}
