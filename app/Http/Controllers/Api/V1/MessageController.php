<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\SendMessageMediaRequest;
use App\Http\Requests\Api\V1\SendMessageRequest;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Media\UploadMediaService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MessageController extends ApiController
{
    /**
     * Queue a text message for delivery via WhatsApp.
     */
    public function store(SendMessageRequest $request)
    {
        $data = $request->validated();

        $company = $this->company($request);

        $phoneNumber = $company->whatsappPhoneNumbers()
            ->where('status', 'connected')
            ->first();

        if (!$phoneNumber) {
            return ApiResponse::error(
                'No connected WhatsApp phone number for this company.',
                409
            );
        }

        $contact = Contact::firstOrCreate(
            ['company_id' => $company->id, 'phone' => $data['to']],
            [
                'name' => $data['name'] ?? null,
                'wa_id' => $data['wa_id'] ?? null,
                'status' => 'active',
            ]
        );

        $conversation = $this->resolveConversation(
            $company->id,
            $phoneNumber,
            $contact
        );

        $message = Message::create([
            'company_id' => $company->id,
            'conversation_id' => $conversation->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_id' => $contact->id,
            'message_template_id' => null,
            'sender_id' => $this->apiKey($request)->created_by,
            'direction' => 'outbound',
            'type' => 'text',
            'status' => 'queued',
            'body' => $data['message'],
            'queued_at' => now(),
        ]);

        SendWhatsAppMessageJob::dispatch($message, $phoneNumber);

                return ApiResponse::data([
            'id' => $message->id,
            'status' => $message->status,
        ], 'Message queued for delivery.', 202);
    }

    /**
     * Show a message (with status history) belonging to the company.
     */
    public function show(Request $request, Message $message)
    {
        if ($message->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Message not found.', 404);
        }

                return ApiResponse::data(
            $message->load(['contact', 'conversation', 'statuses'])
        );
    }

    /**
     * Queue a media message (image, video, audio, document, sticker)
     * for delivery via WhatsApp.
     *
     * The media is first uploaded to Meta to obtain a media ID,
     * then the message is queued for the background worker.
     */
    public function storeMedia(SendMessageMediaRequest $request, UploadMediaService $uploadMedia)
    {
        $data = $request->validated();
        $company = $this->company($request);

        $phoneNumber = $company->whatsappPhoneNumbers()
            ->where('status', 'connected')
            ->first();

        if (!$phoneNumber) {
            return ApiResponse::error(
                'No connected WhatsApp phone number for this company.',
                409
            );
        }

        $contact = Contact::firstOrCreate(
            ['company_id' => $company->id, 'phone' => $data['to']],
            [
                'name' => $data['name'] ?? null,
                'wa_id' => $data['wa_id'] ?? null,
                'status' => 'active',
            ]
        );

        $conversation = $this->resolveConversation(
            $company->id,
            $phoneNumber,
            $contact
        );

        // Upload the media file to Meta to get a media ID.
        try {
            $uploadResponse = $uploadMedia->handle(
                $phoneNumber,
                $data['media_url'],
                $data['type'],
                null
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error(
                'Failed to upload media: ' . $e->getMessage(),
                422
            );
        }

        $mediaId = $uploadResponse['id'] ?? null;

        if (!$mediaId) {
            return ApiResponse::error(
                'Media upload did not return an ID.',
                502
            );
        }

        $message = Message::create([
            'company_id' => $company->id,
            'conversation_id' => $conversation->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_id' => $contact->id,
            'message_template_id' => null,
            'sender_id' => $this->apiKey($request)->created_by,
            'direction' => 'outbound',
            'type' => $data['type'],
            'status' => 'queued',
            'body' => $data['caption'] ?? null,
            'media_url' => $data['media_url'],
            'media_type' => $data['type'],
            'metadata' => ['media_id' => $mediaId],
            'queued_at' => now(),
        ]);

        SendWhatsAppMessageJob::dispatch($message, $phoneNumber);

        return ApiResponse::data([
            'id' => $message->id,
            'status' => $message->status,
            'media_id' => $mediaId,
                ], 'Media message queued for delivery.', 202);
    }

    /**
     * Find (or create) the conversation for a contact + phone number.
     */
    protected function resolveConversation(
        int $companyId,
        WhatsAppPhoneNumber $phoneNumber,
        Contact $contact
    ): Conversation {
        return Conversation::firstOrCreate(
            [
                'company_id' => $companyId,
                'whatsapp_phone_number_id' => $phoneNumber->id,
                'contact_id' => $contact->id,
            ],
            ['status' => 'open']
        );
    }
}



