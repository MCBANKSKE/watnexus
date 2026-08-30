<?php

namespace App\Services\WhatsApp\Authentication;

use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;

/**
 * Verify that a stored WhatsApp access token still works.
 */
class TestWhatsAppConnectionService
{
    use InteractsWithWhatsAppApi;

    /**
     * Ping Meta with the stored token to confirm it is valid.
     */
    public function handle(WhatsAppAccount $account): bool
    {
        if (! $account->business_account_id) {
            return false;
        }

        $response = $this->authenticatedHttp($account->access_token)
            ->get($this->apiBaseUrl().'/'.$account->business_account_id);

        return $response->successful();
    }
}
