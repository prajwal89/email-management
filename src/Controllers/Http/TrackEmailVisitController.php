<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prajwal89\EmailManagement\Models\EmailVisit;
use Prajwal89\EmailManagement\Models\SentEmail;

class TrackEmailVisitController extends Controller
{
    /**
     * Track and redirect any visit from email
     */
    // todo check if user is bot as (email servers check links before serving email to users)
    public function __invoke(Request $request, string $hash)
    {
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

        $sentEmail = SentEmail::query()
            ->where('hash', $hash)
            ->first();

        if (!$sentEmail) {
            Log::warning('Track Visit: Email not found', ['hash' => $hash]);

            return redirect($url, 301);
        }

        $sentEmail->update(['clicked_at' => now()]);

        // Extract the path from the URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';

        EmailVisit::query()->create([
            'path' => $path,
            'ip' => $request->ip(),
            'session_id' => $request->session()->getId(),
            'email_hash' => $hash,
        ]);

        return redirect($url, 301);
    }
}
