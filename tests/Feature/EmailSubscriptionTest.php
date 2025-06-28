<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Models\NewsletterEmail;
use Prajwal89\EmailManagement\Tests\TestCase;

class EmailSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_unsubscribe_from_email()
    {
        $log = EmailLog::factory()->create();

        $this->assertNull($log->unsubscribed_at);

        $signedUrl = URL::temporarySignedRoute(
            'emails.unsubscribe',
            now()->addMinutes(30),
            ['message_id' => $log->message_id]
        );

        $response = $this->get($signedUrl);

        $response->assertSuccessful();

        $log->refresh();

        $this->assertNotNull($log->unsubscribed_at);
    }

    public function test_user_can_unsubscribe_from_newsletter_link()
    {
        $newsletterEmail = NewsletterEmail::factory()->create();

        $this->assertNull($newsletterEmail->unsubscribed_at);

        $signedUrl = URL::temporarySignedRoute(
            'emails.newsletter.unsubscribe',
            now()->addMinutes(30),
            ['encrypted_email' => urlencode(Crypt::encryptString($newsletterEmail->email))]
        );

        $response = $this->get($signedUrl);

        $response->assertRedirect();

        $newsletterEmail->refresh();

        $this->assertNotNull($newsletterEmail->unsubscribed_at);
    }
}
