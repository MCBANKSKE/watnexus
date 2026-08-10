<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEventJob;
use App\Services\WhatsApp\Webhooks\ProcessWhatsAppWebhookService;
use App\Services\WhatsApp\Webhooks\VerifyWebhookSignatureService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Receive WhatsApp Cloud API webhooks from Meta.
 */
class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected VerifyWebhookSignatureService $verifySignature,
        protected ProcessWhatsAppWebhookService $processWebhook
    ) {}

    /**
     * GET /webhooks/whatsapp — Meta's subscription verification handshake.
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $this->verifySignature->verifyChallenge($request)) {
            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Verification failed.', 403);
    }

    /**
     * POST /webhooks/whatsapp — receive webhook events.
     */
    public function receive(Request $request)
    {
        if (!$this->verifySignature->verify($request)) {
            // Always ack to avoid Meta retries when the signature is invalid.
            return response('Invalid signature.', 403);
        }

        $events = $this->processWebhook->handle($request->json()->all());

        // Offload event processing to the queue.
        foreach ($events as $event) {
            ProcessWebhookEventJob::dispatch($event);
        }

        return response()->json([
            'success' => true,
            'received' => count($events),
        ], 200);
    }
}