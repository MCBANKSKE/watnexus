<?php

namespace App\Services\Campaign;

use App\Jobs\FinalizeCampaignJob;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppPhoneNumber;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dispatch a campaign to its recipients.
 *
 * The campaign is marked `running` when dispatched; every recipient
 * message is created in chunked batches and queued for delivery, and
 * a FinalizeCampaignJob completes the campaign once all messages
 * reach a terminal status. Recipients are never fully loaded into
 * memory, so very large campaigns are safe.
 */
class SendCampaignService
{
    /**
     * Number of recipients processed per chunk.
     */
    protected const CHUNK_SIZE = 200;

    public function handle(Campaign $campaign): Campaign
    {
        if ($campaign->isCompleted() || $campaign->isRunning()) {
            throw new RuntimeException('Campaign has already been dispatched.');
        }

        if (! $campaign->messageTemplate?->isApproved()) {
            throw new RuntimeException(
                'Campaign requires an approved message template.'
            );
        }

        $phoneNumber = $this->resolveDefaultPhoneNumber($campaign);

        $campaign->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $total = $this->dispatchToRecipients($campaign, $phoneNumber);

        $campaign->update(['total_recipients' => $total]);

        // Finalize (mark completed) once every message reaches a
        // terminal status — handled asynchronously by the worker.
        FinalizeCampaignJob::dispatch($campaign);

        return $campaign;
    }

    /**
     * Chunk through direct contacts and contact-list members,
     * queueing one message per unique recipient.
     */
    protected function dispatchToRecipients(
        Campaign $campaign,
        WhatsAppPhoneNumber $phoneNumber
    ): int {
        $seenContactIds = [];
        $total = 0;

        $processChunk = function ($contacts) use (
            $campaign,
            $phoneNumber,
            &$seenContactIds,
            &$total
        ): void {
            foreach ($contacts as $contact) {
                if (isset($seenContactIds[$contact->id])) {
                    continue;
                }

                $seenContactIds[$contact->id] = true;
                $total++;

                $this->sendToRecipient($campaign, $phoneNumber, $contact);
            }
        };

        $campaign->contacts()
            ->chunkById(self::CHUNK_SIZE, $processChunk, 'contacts.id');

        foreach ($campaign->contactLists as $list) {
            $list->contacts()
                ->chunkById(self::CHUNK_SIZE, $processChunk, 'contacts.id');
        }

        return $total;
    }

    /**
     * Pick the company's first connected phone number.
     */
    protected function resolveDefaultPhoneNumber(Campaign $campaign): WhatsAppPhoneNumber
    {
        $phoneNumber = WhatsAppAccount::query()
            ->where('company_id', $campaign->company_id)
            ->first()?->phoneNumbers()
            ->where('status', 'connected')
            ->first();

        if (! $phoneNumber) {
            throw new RuntimeException(
                'No connected WhatsApp phone number is available for this campaign.'
            );
        }

        return $phoneNumber;
    }

    /**
     * Persist the recipient message and queue it for delivery.
     */
    protected function sendToRecipient(
        Campaign $campaign,
        WhatsAppPhoneNumber $phoneNumber,
        Contact $contact
    ): void {
        $message = Message::create([
            'company_id' => $campaign->company_id,
            'conversation_id' => $this->resolveConversationId($campaign, $phoneNumber, $contact),
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_id' => $contact->id,
            'message_template_id' => $campaign->message_template_id,
            'sender_id' => $campaign->created_by,
            'direction' => 'outbound',
            'type' => 'template',
            'status' => 'queued',
            'body' => $campaign->messageTemplate?->body,
            'queued_at' => now(),
        ]);

        $this->syncPivot($campaign, $contact, [
            'status' => 'queued',
            'message_id' => $message->id,
            'queued_at' => now(),
        ]);

        // Link the message to the campaign so campaign stats
        // can be updated from webhook status receipts.
        if (! $campaign->messages()->whereKey($message->id)->exists()) {
            $campaign->messages()->attach($message->id);
        }

        // Only dispatch once the surrounding transaction commits
        // (safe even when called outside a transaction).
        DB::afterCommit(function () use ($message, $phoneNumber) {
            SendWhatsAppMessageJob::dispatch($message, $phoneNumber);
        });
    }

    /**
     * Resolve (or create) the conversation for a campaign recipient.
     */
    protected function resolveConversationId(
        Campaign $campaign,
        WhatsAppPhoneNumber $phoneNumber,
        Contact $contact
    ): int {
        $conversation = Conversation::query()->firstOrCreate(
            [
                'company_id' => $campaign->company_id,
                'whatsapp_phone_number_id' => $phoneNumber->id,
                'contact_id' => $contact->id,
            ],
            ['status' => 'open']
        );

        return $conversation->id;
    }

    /**
     * Create or update the campaign_contact pivot row for a recipient.
     */
    protected function syncPivot(
        Campaign $campaign,
        Contact $contact,
        array $attributes
    ): void {
        if ($campaign->contacts()->whereKey($contact->id)->exists()) {
            $campaign->contacts()->updateExistingPivot($contact->id, $attributes);

            return;
        }

        $campaign->contacts()->attach($contact->id, $attributes);
    }
}
