<?php

namespace App\Services\WhatsApp\Authentication;

use Illuminate\Support\Str;

class OAuthConnectService
{
    /**
     * Generate OAuth authorization URL for Meta/Facebook login.
     */
    public function getAuthorizationUrl(string $state = null): string
    {
        $appId = config('services.whatsapp.app_id');
        $redirectUri = route('whatsapp.oauth.callback');
        $state = $state ?? Str::uuid()->toString();
        
        // Use standard OAuth flow without config_id for now
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
     * Generate state parameter for OAuth security.
     */
    public function generateState(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Validate state parameter to prevent CSRF attacks.
     */
    public function validateState(string $state, string $storedState): bool
    {
        return hash_equals($storedState, $state);
    }
}
