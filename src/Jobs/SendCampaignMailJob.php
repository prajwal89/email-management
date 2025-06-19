<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Prajwal89\EmailManagement\Interfaces\EmailReceivable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;


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
