<?php

namespace App\Services\WhatsApp\Authentication;

use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use Illuminate\Support\Str;
use RuntimeException;

class GenerateQrCodeService
{
    use InteractsWithWhatsAppApi;

    /**
     * Generate a QR code for WhatsApp embedded signup.
     */
    public function handle(string $redirectUrl = null): array
    {
        $configId = config('services.whatsapp.oauth_config_id');
        
        if (!$configId) {
            throw new RuntimeException('WhatsApp OAuth Config ID is not configured. Please create an embedded signup configuration in your Meta Business Manager and add WHATSAPP_OAUTH_CONFIG_ID to your .env file.');
        }

        $callbackUrl = $redirectUrl ?? route('whatsapp.qr.callback');
        $sessionId = Str::uuid()->toString();

        // Use the correct Meta API endpoint for embedded signup
        // POST to /{config_id}/accounts generates the QR code
        $response = $this->apiHttp()->post($this->apiBaseUrl() . '/' . $configId . '/accounts', [
            'redirect_uri' => $callbackUrl,
            'session_info' => [
                'session_id' => $sessionId,
            ],
        ]);

        if (!$response->successful()) {
            $error = $response->json();
            throw new RuntimeException('Failed to generate QR code: ' . ($error['error']['message'] ?? $response->body()));
        }

        return [
            'session_id' => $sessionId,
            'qr_code' => $response->json('qr_code_url'),
            'code' => $response->json('code'),
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ];
    }
}
