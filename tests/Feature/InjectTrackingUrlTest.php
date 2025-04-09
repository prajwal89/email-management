<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Prajwal89\EmailManagement\MailHandlers\EmailEvents\UserWelcomeEmailHandler;
use Prajwal89\EmailManagement\Mails\EmailEvents\UserWelcomeEmail;

it('attach tracking url with redirect payload to the email', function (): void {
    Mail::fake();

    $user = User::factory([
        'is_subscribed_for_emails' => 1,
    ])->create();

    // eg. http://127.0.0.1:8000/emails/redirect/KCMfCaNTaERCQZOWd2E4Mb6trFNQG21s?url=http%253A%252F%252F127.0.0.1%253A8000&signature=1dc7e1510cb173a8349d95aad78ce454d86dc476abf816351f43e9977e4bcf19

    (new UserWelcomeEmailHandler($user))->sendEmail();

    Mail::assertQueued(UserWelcomeEmail::class, function ($mail) use ($user) {

        dd(get_class($mail));
        dd($mail->getHeaders());
        dd($mail->render());

        // Assert the recipient is correct
        return $mail->hasTo($user->getEmail());
    });
})->todo();

it('can track email email visits', function (): void {
    // todo
})->todo();

it('cannot track email visits if explicitly instructed', function (): void {
    // todo
})->todo();
