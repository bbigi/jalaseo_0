<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\OAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verifyChallenge($request);
        }

        if (!$this->verifySignature($request)) {
            return response('Invalid signature', 403);
        }

        Log::info('meta_webhook_event', [
            'headers' => $request->headers->all(),
            'body' => $request->json()->all(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function verifyChallenge(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.meta.verify_token')) {
            return response($challenge, 200);
        }

        return response('Invalid verify token', 403);
    }

    private function verifySignature(Request $request): bool
    {
        $signatureHeader = $request->header('X-Hub-Signature-256');
        if (!$signatureHeader) {
            return false;
        }

        $secret = $this->getAppSecret();
        if (!$secret) {
            return false;
        }

        $raw = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $raw, $secret);

        return hash_equals($expected, $signatureHeader);
    }

    private function getAppSecret(): ?string
    {
        $config = OAuthService::getConfig('meta');
        return $config['client_secret'] ?? config('services.meta.app_secret');
    }
}
