<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Services;

use Illuminate\Http\Request;
use League\Uri\Uri;
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

        $uri = Uri::new($this->request->url);

        EmailLogService::update($this->emailLog, ['last_clicked_at' => now()]);

        $this->emailLog->increment('clicks');

        EmailVisitService::store([
            'path' => $uri->getPath(),
            'ip' => $this->request->ip(),
            // ...auth()->check() ? ['user_id' => auth()->user()->id] : [],
            'session_id' => $this->request->hasSession() ? $this->request->session()->getId() : null,
            'message_id' => $this->request->message_id,
        ]);
    }

    public function trackOpen()
    {
        EmailLogService::update($this->emailLog, ['last_opened_at' => now()]);

        $this->emailLog->increment('opens');
    }
}
