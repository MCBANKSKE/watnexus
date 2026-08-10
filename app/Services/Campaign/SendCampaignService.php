<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Messaging\SendTemplateMessageService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dispatch a campaign to its recipients.
 */
class SendCampaignService
{
    public function __construct(
        protected SendTemplateMessageService $sendTemplateMessageService
    ) {}

    /**
     * Send the campaign using the given (or the company's default) phone number.
     */
    public function handle(
        Campaign $campaign,
        ?WhatsAppPhoneNumber $phoneNumber = null
    ): Campaign {
        if ($campaign->isCompleted() || $campaign->isRunning()) {
            throw new RuntimeException('Campaign has already been dispatched.');
        }

        if (!$campaign->messageTemplate?->isApproved()) {
            throw new RuntimeException(
                'Campaign requires an approved message template.'
            );
        }

        $phoneNumber ??= $this->resolveDefaultPhoneNumber($campaign);

        $contacts = $this->resolveRecipients($campaign);

        return DB::transaction(function () use (
            $campaign,
            $phoneNumber,
            $contacts
        ): Campaign {
            $campaign->update([
                'status' => 'running',
                'started_at' => now(),
                'total_recipients' => $contacts->count(),
            ]);

            foreach ($contacts as $contact) {
                $this->sendToRecipient($campaign, $phoneNumber, $contact);
            }

            $campaign->refresh();

            $campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $campaign;
        });
    }

    /**
     * Build the union of direct contacts and contacts from lists.
     *
     * @return \Illuminate\Support\Collection<int, Contact>
     */
    protected function resolveRecipients(Campaign $campaign): \Illuminate\Support\Collection
    {
        $contacts = collect();

        $contacts = $contacts->concat($campaign->contacts->all());

        foreach ($campaign->contactLists as $list) {
            $contacts = $contacts->concat($list->contacts->all());
        }

        return $contacts
            ->unique('id')
            ->values();
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

        if (!$phoneNumber) {
            throw new RuntimeException(
                'No connected WhatsApp phone number is available for this campaign.'
            );
        }

        return $phoneNumber;
    }

    /**
     * Persist the recipient message and send it via WhatsApp.
     */
    protected function sendToRecipient(
        Campaign $campaign,
        WhatsAppPhoneNumber $phoneNumber,
        Contact $contact
    ): void {
        $message = Message::create([
            'company_id' => $campaign->company_id,
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
        if (!$campaign->messages()->whereKey($message->id)->exists()) {
            $campaign->messages()->attach($message->id);
        }

        \App\Jobs\SendWhatsAppMessageJob::dispatch($message, $phoneNumber);
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