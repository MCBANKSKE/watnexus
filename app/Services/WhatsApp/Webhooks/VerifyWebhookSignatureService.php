<?php

namespace App\Services\WhatsApp\Webhooks;

use Illuminate\Http\Request;

/**
 * Verify that a webhook request truly originated from Meta.
 */
class VerifyWebhookSignatureService
{
    /**
     * Check the `X-Hub-Signature-256` HMAC against the payload.
     */
    public function verify(Request $request, ?string $payload = null): bool
    {
        $signature = $request->header('X-Hub-Signature-256');

        if (! $signature) {
            return false;
        }

        $secret = config('services.whatsapp.app_secret');

        if (! $secret) {
            return false;
        }

        $expected = 'sha256='.hash_hmac(
            'sha256',
            $payload ?? (string) $request->getContent(),
            $secret
        );

        return hash_equals($expected, $signature);
    }

    /**
     * Verify the hub.challenge token for webhook subscription setup.
     */
    public function verifyChallenge(Request $request): bool
    {
        $expected = config('services.whatsapp.webhook_verify_token');

        if (! $expected) {
            return false;
        }

        return hash_equals(
            $expected,
            (string) $request->query('hub_verify_token')
        );
    }
}
