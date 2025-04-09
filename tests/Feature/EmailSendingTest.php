<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Enums\ProviderType;
use Prajwal89\EmailManagement\MailHandlers\EmailEvents\UserWelcomeEmailHandler;

test('if email will not be sent to unsubscribed user', function (): void {
    Mail::fake();
    $mail = 'unsubscribed@example.com';

    $user = User::factory()->unverified()->create([
        'email' => $mail,
        'password' => Hash::make('password'),
        'provider_type' => ProviderType::EMAIL,
    ]);

    $user->unsubscribeFromEmails();

    (new UserWelcomeEmailHandler($user))->sendEmail();

    Mail::assertNothingSent();
});

it('can track sent emails in database', function (): void {
    // todo
})->todo();
