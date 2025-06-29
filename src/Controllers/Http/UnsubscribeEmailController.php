<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailLogService;

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
        // todo: should i use this here as this route is also used by the list unsubscribe header
        if ((new CrawlerDetect)->isCrawler()) {
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

        // ! for sample email receivable will not be available
        // handle this explicitly
        $emailLog?->receivable?->unsubscribeFromEmails();

        $isUnsubscribed = EmailLogService::update($emailLog, ['unsubscribed_at' => now()]);

        return view(config('email-management.newsletter_status_view'), [
            'isUnsubscribed' => $isUnsubscribed,
        ]);
    }
}
