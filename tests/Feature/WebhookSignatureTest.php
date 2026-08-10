<?php

namespace Tests\Feature;

use App\Services\WhatsApp\Webhooks\VerifyWebhookSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class WebhookSignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.whatsapp.app_secret', 'test_secret_123');
        config()->set('services.whatsapp.webhook_verify_token', 'my_verify_token');
    }

    private function signedRequest(string $payload, ?string $signature = null): Request
    {
        $secret = config('services.whatsapp.app_secret');
        $sig = $signature ?? 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return Request::create(
            '/api/v1/webhooks/whatsapp',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        )->headers->set('X-Hub-Signature-256', $sig);
    }

    public function test_valid_signature_passes(): void
    {
        $service = app(VerifyWebhookSignatureService::class);
        $payload = json_encode(['entry' => []]);
        $request = $this->signedRequest($payload);

        $this->assertTrue($service->verify($request, $payload));
    }

    public function test_tampered_payload_rejected(): void
    {
        $service = app(VerifyWebhookSignatureService::class);
        $original = json_encode(['entry' => []]);
        $tampered = json_encode(['entry' => ['hacked']]);
        $request = $this->signedRequest($original); // sig for original, not tampered

        $this->assertFalse($service->verify($request, $tampered));
    }

    public function test_missing_signature_header_rejected(): void
    {
        $service = app(VerifyWebhookSignatureService::class);
        $request = Request::create(
            '/api/v1/webhooks/whatsapp',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['entry' => []])
        );

        $this->assertFalse($service->verify($request));
    }

    public function test_missing_app_secret_rejected(): void
    {
        config()->set('services.whatsapp.app_secret', null);

        $service = app(VerifyWebhookSignatureService::class);
        $request = $this->signedRequest(json_encode(['entry' => []]));

        $this->assertFalse($service->verify($request));
    }

    public function test_verify_challenge_with_valid_token(): void
    {
        $service = app(VerifyWebhookSignatureService::class);
        $request = Request::create(
            '/api/v1/webhooks/whatsapp',
            'GET',
            ['hub_mode' => 'subscribe', 'hub_verify_token' => 'my_verify_token', 'hub_challenge' => 'abc123']
        );

        $this->assertTrue($service->verifyChallenge($request));
    }

    public function test_verify_challenge_with_wrong_token(): void
    {
        $service = app(VerifyWebhookSignatureService::class);
        $request = Request::create(
            '/api/v1/webhooks/whatsapp',
            'GET',
            ['hub_mode' => 'subscribe', 'hub_verify_token' => 'wrong_token', 'hub_challenge' => 'abc123']
        );

        $this->assertFalse($service->verifyChallenge($request));
    }

    public function test_webhook_verify_endpoint_returns_challenge(): void
    {
        $response = $this->getJson('/api/v1/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=my_verify_token&hub_challenge=hello_world');

        $response->assertStatus(200);
    }

    public function test_webhook_receive_with_valid_signature(): void
    {
        $payload = json_encode(['entry' => [['changes' => [['field' => 'messages', 'value' => ['messaging_product' => 'whatsapp']]]]]]);
        $secret = config('services.whatsapp.app_secret');
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        $response = $this->withHeader('X-Hub-Signature-256', $signature)
            ->postJson('/api/v1/webhooks/whatsapp', json_decode($payload, true));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_webhook_receive_with_invalid_signature(): void
    {
        $response = $this->withHeader('X-Hub-Signature-256', 'sha256=invalid')
            ->postJson('/api/v1/webhooks/whatsapp', ['entry' => []]);

        $response->assertStatus(403);
    }
}
