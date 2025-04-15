<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
// use App\Services\TurnstileService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Prajwal89\EmailManagement\Mail\NewsletterEmailVerificationEmail;
use Prajwal89\EmailManagement\Models\NewsletterEmail;
use Prajwal89\EmailManagement\Services\NewsletterEmailService;

// todo user should optionally enable the turnstile
class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        // if (!TurnstileService::validateRequest($request)) {
        //     laraToast()->danger('Something went wrong Please Try Again..');

        //     return redirect()->back();
        // }

        $messages = [
            'email.required' => 'We need to know your email address!',
            'email.email' => 'Please provide a valid email address!',
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], $messages);

        if ($validator->fails()) {
            laraToast()->danger($validator->errors()->first());

            return redirect()->back();
        }

        $email = $request->input('email');

        $newsletterEmail = NewsletterEmail::query()
            ->where('email', $email)
            ->first();

        if (!$newsletterEmail) {
            // create new subscription
            $newsletterEmail = NewsletterEmailService::store(['email' => $email]);

            Mail::to($newsletterEmail)->send(new NewsletterEmailVerificationEmail($newsletterEmail));

            laraToast()->success('Verification email has been sent. Please check your inbox.');

            return redirect()->back();
        }

        if ($newsletterEmail->email_verified_at) {
            laraToast()->info('This email is already subscribed.');

            return redirect()->back();
        }

        Mail::to($newsletterEmail)->send(new NewsletterEmailVerificationEmail($newsletterEmail));

        laraToast()->success('A new verification email has been sent. Please check your inbox.');

        return redirect()->back();
    }

    public function confirmSubscription(string $encrypted_email)
    {
        try {
            $email = Crypt::decryptString($encrypted_email);
        } catch (DecryptException $e) {
            // todo report
            abort(404);
        }

        $newsletterEmail = NewsletterEmail::query()
            ->where('email', $email)
            ->first();

        // dd($newsletterEmail);

        if (!$newsletterEmail) {
            laraToast()->danger('Email not found. Please subscribe again.');

            return redirect()->route('home');
        }

        $newsletterEmail->update(['email_verified_at' => now()]);

        laraToast()->success('Email verification successful');

        return redirect()->intended();
    }

    public function unsubscribe(string $encrypted_email)
    {
        try {
            $email = Crypt::decryptString($encrypted_email);
        } catch (DecryptException $e) {
            // todo report
            abort(404);
        }

        $newsletterEmail = NewsletterEmail::query()
            ->where('email', $email)
            ->first();

        if (!$newsletterEmail) {
            laraToast()->danger('Email not found');

            return redirect()->back();
        }

        $newsletterEmail->update(['email_verified_at' => now()]);

        laraToast()->success('Email verification successful');

        return redirect()->intended();
    }
}
