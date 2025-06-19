<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;

class SendCampaignMailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $emailHandlerFqn,
        public EmailReceivable $receivable
    ) {
        //
    }

    public function handle(): void
    {
        $fqn = $this->emailHandlerFqn;

        (new $fqn($this->receivable))->send();
    }
}
