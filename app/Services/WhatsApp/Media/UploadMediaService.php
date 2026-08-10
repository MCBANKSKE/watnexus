<?php

namespace App\Services\WhatsApp\Media;

use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Concerns\InteractsWithWhatsAppApi;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Upload media to WhatsApp Cloud API so it can be referenced
 * by a media ID when sending messages.
 *
 * @return array<string, mixed> Response containing the media ID.
 */
class UploadMediaService
{
    use InteractsWithWhatsAppApi;

    /**
     * Max file size accepted by Meta (~5MB).
     */
    protected int $maxFileSize = 5 * 1024 * 1024;

    /**
     * Upload a local file or URL as media.
     *
     * @param string      $source   Local absolute path or http(s) URL.
     * @param string      $type     MIME type, e.g. image/jpeg.
     * @param string|null $filename Optional filename sent to Meta.
     */
    public function handle(
        WhatsAppPhoneNumber $phoneNumber,
        string $source,
        string $type,
        ?string $filename = null
    ): array {
        $content = $this->readSource($source);

        if (strlen($content) > $this->maxFileSize) {
            throw new RuntimeException(
                'Media exceeds the WhatsApp 5MB upload limit.'
            );
        }

        $filename = $filename ?: basename((string) parse_url($source, PHP_URL_PATH));

        $response = Http::withToken($this->accessTokenFor($phoneNumber))
            ->timeout(60)
            ->asMultipart()
            ->attach('file', $content, $filename)
            ->post(
                $this->apiBaseUrl() . '/' . $phoneNumber->phone_number_id . '/media',
                [
                    'messaging_product' => 'whatsapp',
                    'type' => $type,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Failed to upload WhatsApp media: ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Read the source file/URL into a string.
     */
    protected function readSource(string $source): string
    {
        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            $content = Http::timeout(30)->get($source)->body();

            if (empty($content)) {
                throw new RuntimeException("Unable to download media from: {$source}");
            }

            return $content;
        }

        if (!is_file($source) || !is_readable($source)) {
            throw new RuntimeException("Media file is not readable: {$source}");
        }

        return (string) file_get_contents($source);
    }
}