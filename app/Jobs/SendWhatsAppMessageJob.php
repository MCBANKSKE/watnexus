<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Messaging\SendMediaMessageService;
use App\Services\WhatsApp\Messaging\SendTemplateMessageService;
use App\Services\WhatsApp\Messaging\SendTextMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * @var array<int, int> Backoff in seconds per retry.
     */
    public array $backoff = [15, 60, 300];

    public function __construct(
        public Message $message,
        public WhatsAppPhoneNumber $phoneNumber
    ) {}

    /**
     * Deliver the message via WhatsApp.
     */
        public function handle(
        SendTextMessageService $sendText,
        SendTemplateMessageService $sendTemplate,
        SendMediaMessageService $sendMedia
    ): void {
        if ($this->message->status === 'sent'
            || $this->message->status === 'failed') {
            return;
        }

        try {
            $result = $this->deliver($sendText, $sendTemplate);

            $this->message->updateQuietly([
                'status' => 'sent',
                'whatsapp_message_id' => $result['messages'][0]['id'] ?? null,
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            $this->message->updateQuietly([
                'error_code' => (string) $e->getCode(),
                'error_message' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->message->updateQuietly([
                    'status' => 'failed',
                    'failed_at' => now(),
                ]);

                $this->fail($e);

                return;
            }

            $backoff = $this->backoff[$this->attempts()] ?? 60;

            $this->release($backoff);
        }
    }

    /**
     * Route the send to the correct transport service.
     *
     * @return array<string, mixed>
     */
        protected function deliver(
        SendTextMessageService $sendText,
        SendTemplateMessageService $sendTemplate,
        SendMediaMessageService $sendMedia
    ): array {
        $to = $this->message->contact?->phone
            ?? $this->message->whatsappPhoneNumber?->phone_number;

        if (!$to) {
            throw new \RuntimeException(
                'Cannot resolve a recipient phone number for the message.'
            );
        }

        if ($this->message->type === 'template') {
            return $sendTemplate->handle(
                $this->phoneNumber,
                $to,
                $this->message->messageTemplate?->name ?? '',
                $this->message->messageTemplate?->language ?? 'en',
                $this->message->messageTemplate?->components ?? []
            );
        }

        if (in_array($this->message->type, ['image', 'video', 'audio', 'document', 'sticker'], true)) {
            $mediaId = $this->message->metadata['media_id'] ?? null;

            if (!$mediaId) {
                throw new \RuntimeException(
                    'Media message is missing the uploaded media ID.'
                );
            }

            return $sendMedia->handle(
                $this->phoneNumber,
                $to,
                $mediaId,
                $this->message->type,
                $this->message->body ? ['caption' => $this->message->body] : []
            );
        }

        return $sendText->handle(
            $this->phoneNumber,
            $to,
            (string) $this->message->body
        );
    }
}