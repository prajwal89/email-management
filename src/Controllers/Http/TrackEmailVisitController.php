<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\TrackingService;

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
            'track' => 'sometimes|boolean',
        ]);

        /**
         * Don't track if user is crawler as some
         * email clients crawl links in emails for the security measures
         */
        if ((new CrawlerDetect)->isCrawler()) {
            return;
        }

        if ($validator->fails()) {
            // ? should i throw error
            // Log::warning('Track Email Visit: Validation failed', [
            //     'errors' => $validator->errors()->toArray(),
            //     'request' => $request->all(),
            // ]);

            return abort(400, 'Invalid input');
        }

        $emailLog = EmailLog::query()
            ->where('message_id', $request->message_id)
            ->first();

        if (!$emailLog) {
            // ? should i throw error
            // Log::warning('Track Visit: Email not found', ['message_id' => $request->message_id]);

            return redirect($request->url, 301);
        }

        (new TrackingService($emailLog, $request))->trackVisit();

        return redirect($request->url, 301);
    }
}
