<?php

namespace App\Services\WhatsApp\Authentication;

use App\Models\Company;
use App\Models\WhatsAppAccount;
use Illuminate\Support\Facades\DB;

/**
 * Connect (or update) a WhatsApp Business Account for a company.
 */
class ConnectWhatsAppService
{
    /**
     * Store a connected WhatsApp Business Account.
     */
    public function handle(
        Company $company,
        string $businessAccountId,
        string $accessToken,
        ?string $name = null,
        ?string $tokenExpiresAt = null,
        array $metadata = []
    ): WhatsAppAccount {
        return DB::transaction(function () use (
            $company,
            $businessAccountId,
            $accessToken,
            $name,
            $tokenExpiresAt,
            $metadata
        ): WhatsAppAccount {
            return WhatsAppAccount::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'business_account_id' => $businessAccountId,
                ],
                [
                    'name' => $name,
                    'status' => 'connected',
                    'access_token' => $accessToken,
                    'token_expires_at' => $tokenExpiresAt,
                    'metadata' => $metadata,
                ]
            );
        });
    }

    /**
     * Mark an account as disconnected.
     */
    public function disconnect(WhatsAppAccount $account): bool
    {
        $account->update([
            'status' => 'disconnected',
        ]);

        return true;
    }
}
