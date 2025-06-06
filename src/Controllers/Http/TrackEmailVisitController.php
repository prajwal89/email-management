<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailLogService;
use Prajwal89\EmailManagement\Services\EmailVisitService;

/**
 * Track and redirect any visit from email
 */
class TrackEmailVisitController extends Controller
{
    // todo check if user is bot as (email servers check links before serving email to users)
    public function __invoke(Request $request)
    {
        if (!$request->has('message_id') && empty($request->message_id)) {
            // todo record and redirect
            return;
        }

        $urlBase64 = $request->input('url');

        if (!$urlBase64) {
            Log::warning('Track Visit: Missing URL', ['request' => $request->all()]);

            return abort(400, 'Missing URL');
        }

        // Decode and validate URL
        $url = urldecode($urlBase64);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Log::warning('Track Visit: Invalid URL', ['url' => $urlBase64]);

            return abort(400, 'Malformed URL');
        }

        $url = html_entity_decode($url);

        $emailLog = EmailLog::query()
            ->where('message_id', $request->message_id)
            ->first();

        if (!$emailLog) {
            Log::warning('Track Visit: Email not found', ['message_id' => $request->message_id]);

            return redirect($url, 301);
        }

        EmailLogService::update($emailLog, ['last_clicked_at' => now()]);

        $emailLog->increments('clicks');

        EmailVisitService::store([
            'path' => $request->path(),
            'ip' => $request->ip(),
            'session_id' => $request->session()->getId(),
            'message_id' => $request->message_id,
        ]);

        return redirect($url, 301);
    }
}
