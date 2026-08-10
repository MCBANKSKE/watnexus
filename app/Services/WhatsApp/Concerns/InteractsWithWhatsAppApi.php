<?php

namespace App\Services\WhatsApp\Concerns;

use App\Models\WhatsAppPhoneNumber;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Shared HTTP helpers for the WhatsApp Graph API.
 */
trait InteractsWithWhatsAppApi
{
    /**
     * Build the Graph API base URL using the configured version.
     */
    protected function apiBaseUrl(): string
    {
        return rtrim(config('services.whatsapp.api_url', 'https://graph.facebook.com'), '/')
            . '/' . config('services.whatsapp.graph_version', 'v23.0');
    }

    /**
     * Base HTTP client with JSON/timeout defaults.
     */
    protected function apiHttp(): PendingRequest
    {
        return Http::acceptJson()->timeout(15);
    }

    /**
     * HTTP client with the WhatsApp access token attached.
     */
    protected function authenticatedHttp(string $token): PendingRequest
    {
        return $this->apiHttp()->withToken($token);
    }

    /**
     * Resolve the decrypted access token for a phone number's account.
     */
    protected function accessTokenFor(WhatsAppPhoneNumber $phoneNumber): string
    {
        $account = $phoneNumber->whatsappAccount()->first();

        $token = $account?->access_token;

        if (empty($token)) {
            throw new RuntimeException(
                'WhatsApp access token is missing for phone number '
                . $phoneNumber->phone_number_id . '.'
            );
        }

        return $token;
    }
}