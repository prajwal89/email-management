<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Prajwal89\EmailManagement\Services\FollowUpEmailsSender;

class FollowUpEmailsSenderJob implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        (new FollowUpEmailsSender)->send();
    }
}
