<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Listeners;

use Illuminate\Support\Facades\DB;
use Prajwal89\EmailManagement\Models\HoneypottedIp;
use Spatie\Honeypot\Events\SpamDetectedEvent;

class HoneypotDetectedASpanListener
{
    public function handle(SpamDetectedEvent $event): void
    {
        HoneypottedIp::query()->updateOrCreate([
            'ip' => $event->request->ip(),
        ], [
            'total_requests' => DB::raw('total_requests + 1'),
        ]);
    }
}
