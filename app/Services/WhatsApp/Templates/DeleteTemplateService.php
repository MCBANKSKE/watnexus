<?php

namespace App\Services\WhatsApp\Templates;

use App\Models\MessageTemplate;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;

/**
 * Delete a WhatsApp template from Meta (and mark it disabled locally).
 */
class DeleteTemplateService
{
    use InteractsWithWhatsAppApi;

    /**
     * Delete the template from Meta.
     */
    public function handle(
        WhatsAppAccount $account,
        MessageTemplate $template
    ): bool {
        $endpoint = $template->whatsapp_template_id
            ? $this->apiBaseUrl() . '/' . $template->whatsapp_template_id
            : $this->apiBaseUrl() . '/' . $account->business_account_id
                . '/message_templates?name=' . urlencode($template->name);

        $response = $this->authenticatedHttp($account->access_token)
            ->delete($endpoint);

        $deleted = $response->successful();

        if ($deleted) {
            $template->update(['status' => 'disabled']);
        }

        return $deleted;
    }
}