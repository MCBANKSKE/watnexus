<?php

namespace App\Services\WhatsApp\Authentication;

use App\Models\Company;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VerifyQrCodeConnectionService
{
    use InteractsWithWhatsAppApi;

    /**
     * Verify QR code connection status and create WhatsApp account if successful.
     */
    public function handle(string $sessionId, string $code, Company $company): ?WhatsAppAccount
    {
        $appId = config('services.whatsapp.app_id');
        
        if (!$appId) {
            throw new RuntimeException('WhatsApp App ID is not configured.');
        }

        // Check connection status with Meta
        $response = $this->apiHttp()->get($this->apiBaseUrl() . '/' . $appId . '/whatsapp_embedded_signup', [
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Failed to verify QR code connection: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['waba_id']) || !isset($data['access_token'])) {
            return null; // Connection not yet established
        }

        // Create WhatsApp account with QR code connection data
        return DB::transaction(function () use ($company, $data, $sessionId) {
            $account = WhatsAppAccount::create([
                'company_id' => $company->id,
                'business_account_id' => $data['waba_id'],
                'name' => $data['phone_number'] ?? 'WhatsApp Account',
                'status' => 'connected',
                'connection_method' => 'qr_code',
                'access_token' => $data['access_token'],
                'token_expires_at' => $data['expires_at'] ?? null,
                'qr_code_data' => [
                    'session_id' => $sessionId,
                    'connected_at' => now()->toIso8601String(),
                    'meta_data' => $data,
                ],
                'metadata' => $data,
            ]);

            // Sync phone numbers automatically
            app(SyncWhatsAppPhoneNumbersService::class)->handle($account);

            return $account;
        });
    }

    /**
     * Check if QR code session is still valid.
     */
    public function checkStatus(string $sessionId, string $code): array
    {
        $appId = config('services.whatsapp.app_id');
        
        $response = $this->apiHttp()->get($this->apiBaseUrl() . '/' . $appId . '/whatsapp_embedded_signup', [
            'code' => $code,
        ]);

        if (!$response->successful()) {
            return [
                'status' => 'failed',
                'message' => 'Failed to check status',
            ];
        }

        $data = $response->json();

        return [
            'status' => isset($data['waba_id']) ? 'connected' : 'pending',
            'data' => $data,
        ];
    }
}
