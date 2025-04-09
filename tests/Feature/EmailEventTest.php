<?php

declare(strict_types=1);

use Prajwal89\EmailManagement\Database\Seeders\EmailEventsSeeder;
use Prajwal89\EmailManagement\Models\EmailEvent;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

it('can render all emails of email event', function (): void {
    (new EmailEventsSeeder)->run();

    // todo use factory
    NewsletterEmail::query()->create([
        'email' => fake()->email,
        'email_verified_at' => now(),
    ]);

    EmailEvent::all()
        ->each(function (EmailEvent $emailEvent): void {
            $handler = $emailEvent->resolveEmailHandler();
            expect($handler::renderEmailForPreview())->toBeString();
        });
});

it('cannot send the same email event to the same recipient for the same context', function (): void {
    // todo
})->todo();
