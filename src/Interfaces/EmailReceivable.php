<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Interfaces;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

interface EmailReceivable
{
    public function getName(): string;

    public function getEmail(): string;

    #[Scope]
    public function subscribedToEmails(Builder $query): Builder;

    public function isSubscribedToEmails(): bool;

    public function unsubscribeFromEmails(): bool;
}
