<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailLog;
use Prajwal89\EmailManagement\Services\EmailLogService;

/**
 * Track and record email is opened with pixel
 */
class TrackEmailOpenedController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$request->has('message_id') && empty($request->message_id)) {
            // todo record and redirect
            return;
        }

        $emailLog = EmailLog::query()
            ->where('message_id', $request->message_id)
            ->first();

        if (!$emailLog) {
            Log::warning('Track Opened: Message Id', ['message_id' => $request->message_id]);

            return false;
        }

        EmailLogService::update($emailLog, ['last_opened_at' => now()]);

        $emailLog->increments('opens');

        return true;
    }
}
