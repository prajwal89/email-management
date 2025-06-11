<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Uri;
use Prajwal89\EmailManagement\Models\EmailLog;

class TrackingService
{
    public function __construct(
        public EmailLog $emailLog,
        public Request $request
    ) {
        //
    }

    public function shouldTrack()
    {
        // default to track
        if (!$this->request->has('track')) {
            return true;
        }

        return $this->request->track;
    }

    public function trackVisit()
    {
        // dd($this->shouldTrack());

        $uri = Uri::of($this->request->url);

        EmailLogService::update($this->emailLog, ['last_clicked_at' => now()]);

        $this->emailLog->increment('clicks');

        EmailVisitService::store([
            'path' => $uri->path(),
            'ip' => $this->request->ip(),
            // ...auth()->check() ? ['user_id' => auth()->user()->id] : [],
            'session_id' => $this->request->session()->getId(),
            'message_id' => $this->request->message_id,
        ]);
    }

    public function trackOpen()
    {
        EmailLogService::update($this->emailLog, ['last_opened_at' => now()]);

        $this->emailLog->increment('opens');
    }
}
