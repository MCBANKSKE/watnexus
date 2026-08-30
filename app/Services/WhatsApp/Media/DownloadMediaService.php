<?php

namespace App\Services\WhatsApp\Media;

use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Retrieve (and optionally download) a media object from WhatsApp.
 *
 * @return array<string, mixed> Media metadata (and content if requested).
 */
class DownloadMediaService
{
    use InteractsWithWhatsAppApi;

    /**
     * Resolve the temporary download URL for a media ID.
     */
    public function handle(
        WhatsAppAccount $account,
        string $mediaId,
        bool $fetchContent = false
    ): array {
        $response = $this->authenticatedHttp($account->access_token)
            ->get($this->apiBaseUrl().'/'.$mediaId);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Unable to retrieve WhatsApp media metadata: '.$response->body()
            );
        }

        $metadata = $response->json();

        if (! $fetchContent || empty($metadata['url'])) {
            return $metadata;
        }

        $content = Http::withToken($account->access_token)
            ->timeout(60)
            ->get($metadata['url'])
            ->body();

        return array_merge($metadata, [
            'content' => $content,
        ]);
    }
}
