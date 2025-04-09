<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

class NewsletterEmailService
{
    public static function store(array $attributes): NewsletterEmail
    {
        return NewsletterEmail::query()->create([
            'email' => $attributes['email'],
        ]);
    }

    // todo write url decryption logic in function
    public static function getEmailVerificationUrl(Model $receivable): string
    {
        return URL::signedRoute('emails.newsletter.confirm-subscription', [
            'encrypted_email' => urlencode(Crypt::encryptString($receivable->getEmail())),
        ]);
    }
}
