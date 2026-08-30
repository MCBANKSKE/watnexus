<?php

namespace App\Services\WhatsApp\Messaging;

use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use RuntimeException;

/**
 * Send a plain text WhatsApp message.
 */
class SendTextMessageService
{
    use InteractsWithWhatsAppApi;

    /**
     * Send a text message to a customer.
     */
    public function handle(
        WhatsAppPhoneNumber $phoneNumber,
        string $to,
        string $body,
        bool $previewUrl = false
    ): array {
        $response = $this->authenticatedHttp(
            $this->accessTokenFor($phoneNumber)
        )->post(
            $this->apiBaseUrl().'/'.$phoneNumber->phone_number_id.'/messages',
            [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => $previewUrl,
                    'body' => $body,
                ],
            ]
        );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Failed to send WhatsApp text message: '.$response->body()
            );
        }

        return $response->json();
    }
}
