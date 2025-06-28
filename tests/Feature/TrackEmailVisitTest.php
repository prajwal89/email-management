<?php

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
            'emails.redirect',
            now()->addMinutes(30),
            ['message_id' => $log->message_id, 'url' => $redirectUrl]
        );

        $response = $this->get($signedUrl);

        $response->assertRedirect($redirectUrl);

        $this->assertDatabaseHas('em_email_logs', [
            'id' => $log->id,
            'clicks' => 1,
        ]);
    }
}
