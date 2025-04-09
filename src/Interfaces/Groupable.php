<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface Groupable
{
    public static function getQuery(): Builder;

    public static function getDescription(): string;
}
