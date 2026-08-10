<?php

namespace App\Services\WhatsApp\Messaging;

use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use RuntimeException;

/**
 * Send an approved WhatsApp template message.
 */
class SendTemplateMessageService
{
    use InteractsWithWhatsAppApi;

    /**
     * Send a template message.
     *
     * @param array<int, array<string, mixed>> $components Template components (e.g. body/header).
     */
    public function handle(
        WhatsAppPhoneNumber $phoneNumber,
        string $to,
        string $templateName,
        string $language = 'en',
        array $components = []
    ): array {
        $template = [
            'name' => $templateName,
            'language' => ['code' => $language],
        ];

        if (!empty($components)) {
            $template['components'] = $components;
        }

        $response = $this->authenticatedHttp(
            $this->accessTokenFor($phoneNumber)
        )->post(
            $this->apiBaseUrl() . '/' . $phoneNumber->phone_number_id . '/messages',
            [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => $template,
            ]
        );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Failed to send WhatsApp template message: ' . $response->body()
            );
        }

        return $response->json();
    }
}