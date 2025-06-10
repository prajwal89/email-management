<?php

declare(strict_types=1);

use App\EmailManagement\EmailHandlers\EmailEvents\UserWelcomeEmailHandler;
use App\EmailManagement\Emails\EmailEvents\UserWelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Prajwal89\EmailManagement\Services\EmailContentModifiers;

// ! mock the user welcome email handler
it('attach tracking pixels to the email', function (): void {
    Mail::fake();

    // create new email event for testing
    // then delete it afterwords

    // config(['queue.default' => 'sync']);
    // putenv("QUEUE_CONNECTION=sync");

    $user = User::factory([
        'is_subscribed_for_emails' => 1,
    ])->create();

    // http://127.0.0.1:8000/emails/pixel/KCMfCaNTaERCQZOWd2E4Mb6trFNQG21s
    (new UserWelcomeEmailHandler($user))->send();

    Mail::assertQueued(UserWelcomeEmail::class, function ($mail) use ($user) {
        // ! we can modify email content here and test
        // but this will not test the actual email implementation
        $hash = str()->random(32);

        $updatedHtml = str($mail->render())
            // ->pipe(function ($html) use ($hash) {
            //     return EmailContentModifiers::injectTrackingUrls($html->toString(), $hash);
            // })
            // ->pipe(function ($html) use ($hash): string {
            //     return EmailContentModifiers::injectTrackingPixel($html->toString(), $hash);
            // })
            // ->pipe(function ($html) use ($hash) {
            //     return EmailContentModifiers::injectUnsubscribeLink($html->toString(), $hash);
            // })
            ->toString();

        expect($updatedHtml)->toContain("emails/pixel/$hash");

        // Assert the recipient is correct
        return $mail->hasTo($user->getEmail());
    });
});

it('does not attach tracking pixels if explicitly instructed', function (): void {})->todo();

it('can track email open event', function (): void {
    // todo
})->todo();
