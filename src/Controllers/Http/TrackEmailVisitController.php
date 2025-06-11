<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Uri;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailLogService;
use Prajwal89\EmailManagement\Services\EmailVisitService;

/**
 * Track and redirect any visit from email
 */
class TrackEmailVisitController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|string',
            'url' => 'required|url',
        ]);

        /**
         * Don't track if user is crawler as some 
         * email clients crawl links in emails for the security measures
         */
        if ((new CrawlerDetect())->isCrawler()) {
            return;
        }

        if ($validator->fails()) {
            Log::warning('Track Visit: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all(),
            ]);

            return abort(400, 'Invalid input');
        }

        $emailLog = EmailLog::query()
            ->where('message_id', $request->message_id)
            ->first();

        if (!$emailLog) {
            Log::warning('Track Visit: Email not found', ['message_id' => $request->message_id]);

            return redirect($request->url, 301);
        }

        EmailLogService::update($emailLog, ['last_clicked_at' => now()]);

        $uri = Uri::of($request->url);

        $emailLog->increment('clicks');

        EmailVisitService::store([
            'path' => $uri->path(),
            'ip' => $request->ip(),
            // ...auth()->check() ? ['user_id' => auth()->user()->id] : [],
            'session_id' => $request->session()->getId(),
            'message_id' => $request->message_id,
        ]);

        return redirect($request->url, 301);
    }
}
