<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\TrackingService;

/**
 * Track and record email is opened with pixel
 */
class TrackEmailOpenedController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|string',
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
            Log::warning('Track Open: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all(),
            ]);

            return abort(400, 'Invalid input');
        }

        $emailLog = EmailLog::query()
            ->where('message_id', $request->message_id)
            ->first();

        if (!$emailLog) {
            Log::warning('Track Opened: Message Id', ['message_id' => $request->message_id]);

            return false;
        }

        (new TrackingService($emailLog, $request))->trackOpen();

        return true;
    }
}
