<?php

declare(strict_types=1);

use Prajwal89\EmailManagement\Database\Seeders\EmailCampaignsSeeder;
use Prajwal89\EmailManagement\Models\EmailCampaign;

it('can render all emails of email campaign', function (): void {
    (new EmailCampaignsSeeder)->run();
    EmailCampaign::all()
        ->each(function (EmailCampaign $emailCampaign): void {
            $handler = $emailCampaign->resolveEmailHandler();
            expect($handler::renderEmailForPreview())->toBeString();
        });
});

it('can run campaign', function (): void {
    // todo
})->todo();
