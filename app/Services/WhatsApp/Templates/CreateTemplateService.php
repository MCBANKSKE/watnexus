<?php

namespace App\Services\WhatsApp\Templates;

use App\Models\MessageTemplate;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use RuntimeException;

/**
 * Submit a new WhatsApp message template to Meta for review.
 */
class CreateTemplateService
{
    use InteractsWithWhatsAppApi;

    /**
     * Create a template under the account's WhatsApp Business Account.
     *
     * @param array<int, array<string, mixed>> $components
     *
     * @return array<string, mixed> Meta response
     */
    public function handle(
        WhatsAppAccount $account,
        string $name,
        string $language,
        string $category,
        array $components,
        ?array $allowCategoryChange = null
    ): array {
        $payload = [
            'name' => $name,
            'language' => $language,
            'category' => $category,
            'components' => $components,
        ];

        if (is_array($allowCategoryChange) && count($allowCategoryChange) > 0) {
            $payload['allow_category_change'] = $allowCategoryChange;
        }

        $response = $this->authenticatedHttp($account->access_token)
            ->post(
                $this->apiBaseUrl() . '/' . $account->business_account_id . '/message_templates',
                $payload
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Failed to create WhatsApp template: ' . $response->body()
            );
        }

        return $response->json();
    }
}