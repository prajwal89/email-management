<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Model;

class HoneypottedIp extends Model
{
    protected $table = 'em_honeypotted_ips';

    protected $fillable = [
        'ip',
        'total_requests',
    ];
}
