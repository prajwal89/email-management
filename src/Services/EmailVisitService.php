<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Prajwal89\EmailManagement\Models\EmailVisit;

class EmailVisitService
{
    // we can invoke events from here
    public static function store(array $attributes)
    {
        return EmailVisit::create($attributes);
    }
}
