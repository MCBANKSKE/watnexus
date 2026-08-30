<?php

namespace App\Services\WhatsApp\Authentication;

use App\Models\Company;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OAuthCallbackService
{
    use InteractsWithWhatsAppApi;

    /**
     * Handle OAuth callback and create WhatsApp account.
     */
    public function handle(string $code, Company $company): WhatsAppAccount
    {
        $appId = config('services.whatsapp.app_id');
        $appSecret = config('services.whatsapp.app_secret');
        $redirectUri = route('whatsapp.oauth.callback');

        if (!$appId || !$appSecret) {
            throw new RuntimeException('WhatsApp App ID or Secret is not configured.');
        }

        // Exchange authorization code for access token
        $response = $this->apiHttp()->post('https://graph.facebook.com/v23.0/oauth/access_token', [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Failed to exchange authorization code: ' . $response->body());
        }

        $tokenData = $response->json();

        if (!isset($tokenData['access_token'])) {
            throw new RuntimeException('No access token received from Meta.');
        }

        // Get user info and WhatsApp business accounts
        $userResponse = $this->authenticatedHttp($tokenData['access_token'])
            ->get('https://graph.facebook.com/v23.0/me?fields=id,name');

        if (!$userResponse->successful()) {
            throw new RuntimeException('Failed to fetch user info: ' . $userResponse->body());
        }

        $userData = $userResponse->json();

        // Get WhatsApp business accounts
        $wabaResponse = $this->authenticatedHttp($tokenData['access_token'])
            ->get('https://graph.facebook.com/v23.0/me/subscribed_apps');

        if (!$wabaResponse->successful()) {
            throw new RuntimeException('Failed to fetch WhatsApp business accounts: ' . $wabaResponse->body());
        }

        $wabaData = $wabaResponse->json();

        // Create WhatsApp account with OAuth connection data
        return DB::transaction(function () use ($company, $tokenData, $userData, $wabaData) {
            $wabaId = $wabaData['data'][0]['waba_id'] ?? null;

            if (!$wabaId) {
                throw new RuntimeException('No WhatsApp Business Account found for this user.');
            }

            $account = WhatsAppAccount::create([
                'company_id' => $company->id,
                'business_account_id' => $wabaId,
                'name' => $userData['name'] . "'s WhatsApp Account",
                'status' => 'connected',
                'connection_method' => 'oauth',
                'access_token' => $tokenData['access_token'],
                'token_expires_at' => $tokenData['expires_in'] 
                    ? now()->addSeconds($tokenData['expires_in']) 
                    : null,
                'oauth_user_id' => $userData['id'],
                'oauth_token' => $tokenData['access_token'],
                'metadata' => [
                    'user_data' => $userData,
                    'waba_data' => $wabaData,
                    'token_data' => $tokenData,
                ],
            ]);

            // Sync phone numbers automatically
            app(SyncWhatsAppPhoneNumbersService::class)->handle($account);

            return $account;
        });
    }

    /**
     * Refresh OAuth access token.
     */
    public function refreshToken(WhatsAppAccount $account): string
    {
        $appId = config('services.whatsapp.app_id');
        $appSecret = config('services.whatsapp.app_secret');

        $response = $this->apiHttp()->post('https://graph.facebook.com/v23.0/oauth/access_token', [
            'grant_type' => 'refresh_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'refresh_token' => $account->oauth_token,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Failed to refresh token: ' . $response->body());
        }

        $tokenData = $response->json();

        $account->update([
            'access_token' => $tokenData['access_token'],
            'oauth_token' => $tokenData['access_token'],
            'token_expires_at' => $tokenData['expires_in'] 
                ? now()->addSeconds($tokenData['expires_in']) 
                : null,
        ]);

        return $tokenData['access_token'];
    }
}
