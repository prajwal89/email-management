<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Prajwal89\EmailManagement\Models\EmailLog;

class UnsubscribeEmailController extends Controller
{
    // todo show view file with alert notification
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|string',
        ]);

        /**
         * Don't track if user is crawler as some 
         * email clients crawl links in emails for the security measures
         */
        if ((new CrawlerDetect())->isCrawler()) {
            return;
        }

        if ($validator->fails()) {
            Log::warning('Unsubscribe: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all(),
            ]);

            return abort(400, 'Invalid input');
        }

        $emailLog = EmailLog::query()
            ->where('message_id', $request->message_id)
            ->first();

        if (!$emailLog) {
            Log::warning('UnSubscribe: Message Id', ['message_id' => $request->message_id]);

            return redirect()->route('home');
        }

        $emailLog->receivable->unsubscribeFromEmails();

        return response(status: 200);
    }
}
