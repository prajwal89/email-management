<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailVisit;
use Prajwal89\EmailManagement\Models\SentEmail;

class TrackEmailOpenedController extends Controller
{
    /**
     * Track and record email is opened with pixel
     */
    public function __invoke(Request $request, string $hash): bool
    {
        $sentEmail = SentEmail::query()
            ->where('hash', $hash)
            ->first();

        if (!$sentEmail) {
            Log::warning('Track Opened: Invalid hash', ['hash' => $hash]);

            return false;
        }

        $sentEmail->update(['opened_at' => now()]);

        return true;
    }
}
