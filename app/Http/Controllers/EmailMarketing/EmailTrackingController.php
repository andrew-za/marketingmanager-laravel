<?php

namespace App\Http\Controllers\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Services\EmailMarketing\EmailSendingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EmailTrackingController extends Controller
{
    public function __construct(
        private EmailSendingService $emailSendingService
    ) {}

    public function trackOpen(Request $request, string $token): \Illuminate\Http\Response
    {
        $this->emailSendingService->trackOpen($token);

        // Return 1x1 transparent PNG pixel
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return response($pixel, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($pixel),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function trackClick(Request $request, string $token): \Illuminate\Http\RedirectResponse
    {
        $url = $request->get('url');
        
        if (!$url) {
            abort(404);
        }

        $this->emailSendingService->trackClick($token, urldecode($url));

        return Redirect::away(urldecode($url));
    }

    public function unsubscribe(Request $request, string $token): JsonResponse|\Illuminate\View\View
    {
        $this->emailSendingService->trackUnsubscribe($token);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'You have been unsubscribed successfully.',
            ]);
        }

        return view('emails.unsubscribed');
    }
}

