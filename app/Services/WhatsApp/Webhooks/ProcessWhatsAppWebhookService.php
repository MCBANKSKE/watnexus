<?php

namespace App\Services\WhatsApp\Webhooks;

use App\Jobs\ProcessWebhookEventJob;
use App\Models\WebhookEvent;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppPhoneNumber;

/**
 * Store an incoming Meta webhook payload as a WebhookEvent
 * and dispatch a background job to process it.
 *
 * @return array<int, WebhookEvent> Created events.
 */
class ProcessWhatsAppWebhookService
{
    /**
     * Parse the raw webhook body and persist one event per change.
     */
    public function handle(array $payload): array
    {
        $events = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            $account = $this->resolveAccount($entry['id'] ?? null);

            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                $metadata = $value['metadata'] ?? [];

                $phoneNumber = $this->resolvePhoneNumber(
                    $metadata['phone_number_id'] ?? null
                );

                $event = WebhookEvent::create([
                    'company_id' => $account?->company_id,
                    'whatsapp_account_id' => $account?->id,
                    'whatsapp_phone_number_id' => $phoneNumber?->id,
                    'event_type' => $change['field'] ?? 'unknown',
                    'event_id' => $this->resolveEventId($value),
                    'status' => 'received',
                    'attempts' => 0,
                    'payload' => $value,
                ]);

                ProcessWebhookEventJob::dispatch($event);

                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Find the account by its Meta WABA ID.
     */
    protected function resolveAccount(?string $businessAccountId): ?WhatsAppAccount
    {
        if (! $businessAccountId) {
            return null;
        }

        return WhatsAppAccount::query()
            ->where('business_account_id', $businessAccountId)
            ->first();
    }

    /**
     * Find the phone number by its Meta phone number ID.
     */
    protected function resolvePhoneNumber(?string $phoneNumberId): ?WhatsAppPhoneNumber
    {
        if (! $phoneNumberId) {
            return null;
        }

        return WhatsAppPhoneNumber::query()
            ->where('phone_number_id', $phoneNumberId)
            ->first();
    }

    /**
     * Derive a stable event ID for deduplication.
     */
    protected function resolveEventId(array $value): ?string
    {
        return $value['messages'][0]['id']
            ?? $value['statuses'][0]['id']
            ?? $value['message_template_id']
            ?? null;
    }
}
