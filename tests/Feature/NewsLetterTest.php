<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Prajwal89\EmailManagement\Mails\EmailEvents\NewsletterEmailVerificationEmail;
use Prajwal89\EmailManagement\Models\NewsletterEmail;
use Prajwal89\EmailManagement\Services\NewsletterEmailService;

// todo implement in module of starter template then import here

it('can send newsletter verification email', function (): void {
    Mail::fake();

    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $data = [
        'email' => 'hi@newsletter.com',
    ];

    $this->post(route('emails.newsletter.subscribe'), $data);

    $this->assertDatabaseHas('em_newsletter_emails', $data);

    Mail::assertSent(NewsletterEmailVerificationEmail::class);
})->todo();

it('can get verified using verification link ', function (): void {
    $email = 'hi2@newsletter.com';

    $data = ['email' => $email];

    $this->post(route('emails.newsletter.subscribe'), $data);

    $newsletterEmail = NewsletterEmail::query()
        ->where('email', $email)
        ->first();

    $verificationUrl = NewsletterEmailService::getEmailVerificationUrl($newsletterEmail);

    $this->get($verificationUrl);

    $newsletterEmail = NewsletterEmail::query()
        ->where('email', $email)
        ->whereNotNull('email_verified_at')
        ->first();

    expect($newsletterEmail)->not->toBeNull();
})->todo();

test('user can unsubscribe from newsletter', function (): void {
    $email = 'hi3@newsletter.com';

    $newsletter = NewsletterEmail::query()->create([
        'email' => $email,
    ]);

    //

})->todo();

test('we can send email to the subscribed user', function (): void {
    //
})->todo();

test('if email will not be sent to unsubscribed user', function (): void {})->todo();

test('if newsletter email verification route is rate limited', function (): void {
    //
    $data = [
        'email' => 'hirate_limited@newsletter.com',
    ];

    for ($i = 0; $i < 2; $i++) {
        $this->post(route('emails.newsletter.subscribe'), $data);
    }

    $response = $this->post(route('emails.newsletter.subscribe'), $data);

    $response->assertStatus(429);
});
