<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Interfaces;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface EmailSendable
{
    public function emailLogs(): MorphMany;

    public function emailVisits(): HasManyThrough;

    public function emailVariants(): MorphMany;

    public function defaultEmailVariant(): MorphOne;
}
