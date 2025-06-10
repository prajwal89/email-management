<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailLog;

class UnsubscribeEmailController extends Controller
{
    // todo check for bot (only if get request)
    // todo show view file with alert notification
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
            Log::warning('UnSubscribe: Message Id', ['message_id' => $request->message_id]);

            return redirect()->route('home');
        }

        $emailLog->receivable->unsubscribeFromEmails();

        return response(status: 200);
    }
}
