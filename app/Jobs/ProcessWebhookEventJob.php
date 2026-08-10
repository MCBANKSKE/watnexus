<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WebhookEvent;
use App\Models\WhatsAppPhoneNumber;
use App\Services\Messaging\MessageStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Consume a stored webhook event and apply it to our data
 * (message statuses, inbound messages, campaign stats).
 */
class ProcessWebhookEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * @var array<int, int>
     */
    public array $backoff = [15, 60, 300];

    public function __construct(
        public WebhookEvent $event
    ) {}

    public function handle(MessageStatusService $statusService): void
    {
        if ($this->event->status === 'processed') {
            return;
        }

        $this->event->update([
            'status' => 'processing',
            'attempts' => $this->event->attempts + 1,
        ]);

        try {
            $this->dispatchType(
                $this->event->payload,
                $statusService
            );

            $this->event->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Webhook event processing failed', [
                'event' => $this->event->id,
                'error' => $e->getMessage(),
            ]);

            $this->event->update([
                'error_message' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->event->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                ]);

                $this->fail($e);

                return;
            }

            $this->release($this->backoff[$this->attempts()] ?? 60);
        }
    }

    /**
     * Route the change to the right handler.
     *
     * @param array<string, mixed> $value
     */
    protected function dispatchType(array $value, MessageStatusService $statusService): void
    {
        if (!empty($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleStatusUpdate($status, $statusService);
            }
        }

        if (!empty($value['messages'])) {
            foreach ($value['messages'] as $inbound) {
                $this->handleInboundMessage($value, $inbound);
            }
        }
    }

    /**
     * Apply a delivery/read/failed receipt to an outbound message.
     *
     * @param array<string, mixed> $statusReceipt
     */
    protected function handleStatusUpdate(
        array $statusReceipt,
        MessageStatusService $statusService
    ): void {
        $whatsappMessageId = $statusReceipt['id'] ?? null;

        if (!$whatsappMessageId) {
            return;
        }

        $message = Message::query()
            ->where('whatsapp_message_id', $whatsappMessageId)
            ->first();

        if (!$message) {
            return;
        }

        $status = strtolower((string) ($statusReceipt['status'] ?? ''));

        $applied = $statusService->applyStatus(
            $message,
            $status,
            strtoupper($whatsappMessageId),
            $statusReceipt['errors'][0]['code'] ?? null,
            $statusReceipt['errors'][0]['error_data']['details'] ?? null,
            $statusReceipt
        );

        if ($applied) {
            $this->updateCampaignStats($message);
        }
    }
/**
     * Store an inbound message and ensure its contact + conversation exist.
     *
     * @param array<string, mixed> $value
     * @param array<string, mixed> $inbound
     */
    protected function handleInboundMessage(array $value, array $inbound): void
    {
        $phoneNumber = $this->phoneNumber($value);

        if (!$phoneNumber) {
            return;
        }

        $waId = $value['contacts'][0]['wa_id'] ?? $inbound['from'] ?? null;

        if (!$waId) {
            return;
        }

        $conversation = $this->resolveConversation($phoneNumber, $waId);

        $message = Message::firstOrCreate(
            ['whatsapp_message_id' => $inbound['id'] ?? null],
            [
                'company_id' => $phoneNumber->company_id,
                'conversation_id' => $conversation->id,
                'whatsapp_phone_number_id' => $phoneNumber->id,
                'contact_id' => $conversation->contact_id,
                'direction' => 'inbound',
                'type' => $this->inboundType($inbound),
                'status' => 'delivered',
                'body' => $this->inboundBody($inbound),
                'delivered_at' => now(),
                'metadata' => $inbound,
            ]
        );

        if (!$message->wasRecentlyCreated) {
            return;
        }

        $conversation->updateQuietly([
            'last_message' => $message->body,
            'last_message_at' => now(),
            'last_message_direction' => 'inbound',
            'unread_count' => $conversation->unread_count + 1,
        ]);
    }

    protected function phoneNumber(array $value): ?WhatsAppPhoneNumber
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        return $phoneNumberId
            ? WhatsAppPhoneNumber::query()
                ->where('phone_number_id', $phoneNumberId)
                ->first()
            : null;
    }

    /**
     * Find the inbound WhatsApp user; create a contact + conversation if needed.
     */
    protected function resolveConversation(
        WhatsAppPhoneNumber $phoneNumber,
        string $waId
    ): Conversation {
        $contact = Contact::query()->firstOrCreate(
            [
                'company_id' => $phoneNumber->company_id,
                'wa_id' => $waId,
            ],
            [
                'phone' => $waId,
                'status' => 'active',
            ]
        );

        return Conversation::query()->firstOrCreate(
            [
                'company_id' => $phoneNumber->company_id,
                'whatsapp_phone_number_id' => $phoneNumber->id,
                'contact_id' => $contact->id,
            ],
            ['status' => 'open']
        );
    }

    /**
     * @param array<string, mixed> $inbound
     */
    protected function inboundType(array $inbound): string
    {
        $type = strtolower((string) ($inbound['type'] ?? 'text'));

        return in_array($type, [
            'text', 'template', 'image', 'video', 'audio',
            'document', 'location', 'interactive', 'sticker',
        ], true)
            ? $type
            : 'text';
    }

    /**
     * @param array<string, mixed> $inbound
     */
    protected function inboundBody(array $inbound): ?string
    {
        return match (strtolower((string) ($inbound['type'] ?? 'text'))) {
            'text' => $inbound['text']['body'] ?? null,
            'button' => $inbound['button']['text'] ?? null,
            'interactive' => $inbound['interactive']['button_reply']['title']
                ?? $inbound['interactive']['list_reply']['title']
                ?? null,
            default => null,
        };
    }

    /**
     * Reflect delivery/read/failure onto campaign counters.
     */
    protected function updateCampaignStats(Message $message): void
    {
        $campaign = $message->campaigns()->first();

        if (!$campaign) {
            return;
        }

        $campaign->refresh();

        if ($message->status === 'read') {
            $campaign->increment('read_count');

            return;
        }

        if ($message->status === 'failed') {
            $campaign->increment('failed_count');
        }
    }
}