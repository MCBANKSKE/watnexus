<?php

namespace App\Services\WhatsApp\Authentication;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class OAuthConnectService
{
    /**
     * Generate OAuth authorization URL for Meta/Facebook login.
     */
    public function getAuthorizationUrl(string $state = null): string
    {
        $appId = config('services.whatsapp.app_id');
        $redirectUri = route('whatsapp.oauth.callback');
        $state = $state ?? $this->generateState();

        $scopes = [
            'whatsapp_business_management',
            'whatsapp_business_messaging',
            'whatsapp_business_messaging_phone_number',
        ];

        $params = [
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
            'state' => $state,
        ];

        return 'https://www.facebook.com/v23.0/dialog/oauth?' . http_build_query($params);
    }

    /**
     * Generate an encrypted state parameter carrying the company context.
     *
     * The state is decrypted on callback to identify the company without
     * relying on the web session (Meta's redirect carries no auth token).
     */
    public function generateState(int $companyId): string
    {
        $payload = [
            'company_id' => $companyId,
            'nonce' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(15)->getTimestamp(),
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Decrypt and validate a state parameter from the OAuth callback.
     *
     * @return array{company_id: int}|null Null when the state is invalid,
     *                                    tampered with, or expired.
     */
    public function resolveState(string $state): ?array
    {
        try {
            $payload = json_decode(Crypt::decryptString($state), true);
        } catch (\Throwable) {
            return null;
        }

        if (
            !is_array($payload)
            || !isset($payload['company_id'], $payload['nonce'], $payload['expires_at'])
            || !is_numeric($payload['company_id'])
            || !is_numeric($payload['expires_at'])
            || $payload['expires_at'] < now()->getTimestamp()
        ) {
            return null;
        }

        return ['company_id' => (int) $payload['company_id']];
    }

    /**
     * Validate state parameter (legacy hash comparison, kept for compatibility).
     */
    public function validateState(string $state, string $storedState): bool
    {
        return hash_equals($storedState, $state);
    }
}
