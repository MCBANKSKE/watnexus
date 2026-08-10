<?php

namespace App\Services\WhatsApp\Authentication;

use App\Models\WhatsAppAccount;
use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use RuntimeException;

/**
 * Pull the phone numbers registered under a WhatsApp Business
 * Account from Meta and upsert them into our database.
 */
class SyncWhatsAppPhoneNumbersService
{
    use InteractsWithWhatsAppApi;

    /**
     * Fetch and persist the phone numbers for the given account.
     *
     * @return array<int, WhatsAppPhoneNumber>
     */
    public function handle(WhatsAppAccount $account): array
    {
        $data = $this->fetchPhoneNumbers($account);

        $saved = [];

        foreach ($data as $phone) {
            $saved[] = WhatsAppPhoneNumber::updateOrCreate(
                [
                    'phone_number_id' => $phone['id'],
                ],
                [
                    'company_id' => $account->company_id,
                    'whatsapp_account_id' => $account->id,
                    'phone_number' => $phone['display_phone_number'] ?? '',
                    'display_name' => $phone['verified_name'] ?? null,
                    'status' => 'connected',
                    'quality_rating' => $phone['quality_rating'] ?? null,
                    'messaging_limit' => $phone['messaging_limit'] ?? null,
                    'country_code' => $phone['country_code'] ?? null,
                    'metadata' => $phone,
                ]
            );
        }

        return $saved;
    }

    /**
     * Retrieve the phone number list from Meta.
     */
    protected function fetchPhoneNumbers(WhatsAppAccount $account): array
    {
        if (!$account->access_token) {
            throw new RuntimeException('WhatsApp access token is missing.');
        }

        if (!$account->business_account_id) {
            throw new RuntimeException('WhatsApp Business Account ID is missing.');
        }

        $response = $this->authenticatedHttp($account->access_token)
            ->get(
                $this->apiBaseUrl() . '/' .
                $account->business_account_id . '/phone_numbers'
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Unable to retrieve WhatsApp phone numbers: '
                . $response->body()
            );
        }

        return $response->json('data', []);
    }
}