<?php

namespace App\Services\WhatsApp\Templates;

use App\Models\MessageTemplate;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use RuntimeException;

/**
 * Pull all templates from Meta and upsert them into our database.
 */
class SyncTemplatesService
{
    use InteractsWithWhatsAppApi;

    /**
     * Fetch and persist every template under the account's WABA.
     *
     * @return array<int, MessageTemplate>
     */
    public function handle(WhatsAppAccount $account): array
    {
        $templates = $this->fetchTemplates($account);

        $saved = [];

        foreach ($templates as $template) {
            $language = null;
            if (isset($template['language']) && is_string($template['language'])) {
                $language = $template['language'];
            }

            $saved[] = MessageTemplate::updateOrCreate(
                [
                    'company_id' => $account->company_id,
                    'name' => $template['name'] ?? null,
                    'language' => $language ?? 'en',
                ],
                [
                    'whatsapp_template_id' => $template['id'] ?? null,
                    'category' => $template['category'] ?? 'utility',
                    'status' => $this->normalizeStatus($template['status'] ?? 'pending'),
                    'components' => $template['components'] ?? null,
                    'metadata' => $template,
                ]
            );
        }

        return $saved;
    }

    /**
     * Retrieve the template list from Meta.
     */
    protected function fetchTemplates(WhatsAppAccount $account): array
    {
        $response = $this->authenticatedHttp($account->access_token)
            ->get(
                $this->apiBaseUrl().'/'.$account->business_account_id.'/message_templates',
                [
                    'fields' => 'id,name,status,category,language,components,rejected_reason',
                ]
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Unable to retrieve WhatsApp templates: '.$response->body()
            );
        }

        return $response->json('data', []);
    }

    /**
     * Map Meta's status to our template status enum.
     */
    protected function normalizeStatus(string $status): string
    {
        return match (strtolower($status)) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'disabled' => 'disabled',
            'in_appeal' => 'pending',
            'paused' => 'disabled',
            default => 'pending',
        };
    }
}
