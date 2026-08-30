<?php

namespace App\Services\WhatsApp\Messaging;

use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use Illuminate\Support\Facades\Http;

/**
 * Mark an inbound WhatsApp message as read.
 */
class MarkMessageAsReadService
{
    use InteractsWithWhatsAppApi;

    /**
     * Send the "read" receipt to Meta for a message ID.
     */
    public function handle(
        WhatsAppPhoneNumber $phoneNumber,
        string $whatsappMessageId
    ): bool {
        $response = Http::withToken($this->accessTokenFor($phoneNumber))
            ->acceptJson()
            ->timeout(15)
            ->post(
                $this->apiBaseUrl().'/'.$phoneNumber->phone_number_id.'/messages',
                [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $whatsappMessageId,
                ]
            );

        return $response->successful();
    }
}
