<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\SentEmail;

class UnsubscribeEmailController extends Controller
{
    public function unsubscribe(Request $request, string $hash)
    {
        // todo check for bot (only if get request)
        // as email clients check links before
        // dd($hash);

        $sentEmail = SentEmail::query()
            ->where('hash', $hash)
            ->first();

        if (!$sentEmail) {
            Log::warning('Unsubscribe: Invalid hash', ['hash' => $hash]);

            return response(status: 400);
        }

        $sentEmail->receivable->unsubscribeFromEmails();

        return response(status: 200);
    }
}
