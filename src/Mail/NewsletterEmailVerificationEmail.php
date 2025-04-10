<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Prajwal89\EmailManagement\Services\NewsletterEmailService;

class NewsletterEmailVerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    public function __construct(public Model $receivable)
    {
        $this->verificationUrl = NewsletterEmailService::getEmailVerificationUrl($receivable);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Newsletter Verification ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'email-management::emails.email-events.newsletter-email-verification-email'
        );
    }
}
