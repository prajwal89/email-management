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
        $uri = Uri::of($this->request->url);

        EmailLogService::update($this->emailLog, ['last_clicked_at' => now()]);

        $this->emailLog->increment('clicks');

        EmailVisitService::store([
            'path' => $uri->path(),
            'ip' => $this->request->ip(),
            'session_id' => $this->request->hasSession() ? $this->request->session()->getId() : null,
            'message_id' => $this->request->message_id,
            ...auth()->check()
                ? ['user_id' => auth()->user()->id]
                : [],
        ]);
    }

    public function trackOpen()
    {
        EmailLogService::update($this->emailLog, ['last_opened_at' => now()]);

        $this->emailLog->increment('opens');
    }
}
