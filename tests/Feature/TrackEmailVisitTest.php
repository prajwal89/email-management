<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Tests\TestCase;

class TrackEmailVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_track_email_visits()
    {
        $log = EmailLog::factory()->create();

        $redirectUrl = 'https://example.com';

        $signedUrl = URL::temporarySignedRoute(
            name: 'emails.redirect',
            expiration: now()->addMinutes(30),
            parameters: [
                'message_id' => $log->message_id,
                'url' => $redirectUrl,
            ]
        );

        $response = $this->get($signedUrl);

        $response->assertRedirect($redirectUrl);

        $this->assertDatabaseHas('em_email_logs', [
            'message_id' => $log->message_id,
            'clicks' => 1,
        ]);
    }

    public function test_it_can_track_email_opens()
    {
        $log = EmailLog::factory()->create();

        $this->assertNull($log->last_opened_at);

        $this->assertEquals(0, $log->opens);

        $response = $this->get(route('emails.pixel', ['message_id' => $log->message_id]));

        $response->assertSuccessful();

        $log->refresh();

        $this->assertNotNull($log->last_opened_at);

        $this->assertEquals(1, $log->opens);
    }
}
