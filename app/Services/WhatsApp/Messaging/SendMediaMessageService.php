<?php

namespace App\Services\WhatsApp\Messaging;

use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use RuntimeException;

/**
 * Send a media WhatsApp message (image, video, audio, document, sticker).
 */
class SendMediaMessageService
{
    use InteractsWithWhatsAppApi;

    /**
     * Supported message types by Meta.
     *
     * @var list<string>
     */
    protected array $supportedTypes = [
        'image',
        'video',
        'audio',
        'document',
        'sticker',
    ];

    /**
     * Send a media message using an already-uploaded media ID.
     *
     * @param  array<string, mixed>  $options  Extra media attributes (caption, filename, ...).
     */
    public function handle(
        WhatsAppPhoneNumber $phoneNumber,
        string $to,
        string $mediaId,
        string $type,
        array $options = []
    ): array {
        if (! in_array($type, $this->supportedTypes, true)) {
            throw new RuntimeException("Unsupported WhatsApp media type: {$type}");
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $type,
            $type => array_merge(
                ['id' => $mediaId],
                $options
            ),
        ];

        $response = $this->authenticatedHttp(
            $this->accessTokenFor($phoneNumber)
        )->post(
            $this->apiBaseUrl().'/'.$phoneNumber->phone_number_id.'/messages',
            $payload
        );

        if (! $response->successful()) {
            throw new RuntimeException(
                "Failed to send WhatsApp {$type} message: ".$response->body()
            );
        }

        return $response->json();
    }
}
