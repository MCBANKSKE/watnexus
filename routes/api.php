<?php

use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Http\Controllers\Api\V1\TemplateController;
use App\Http\Controllers\Webhook\WhatsAppWebhookController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versioned, API-key authenticated, rate-limited endpoints.
|
*/

Route::get('v1/status', function () {
    return ApiResponse::data([
        'service' => 'watnexus-api',
        'version' => 'v1',
        'time' => now()->toIso8601String(),
    ], 'API is healthy.');
})->name('api.v1.status');

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| These endpoints receive inbound webhooks from Meta (WhatsApp Cloud API).
| They are publicly accessible — security is provided by the
| X-Hub-Signature-256 HMAC verification in the controller layer.
|
*/

Route::prefix('v1/webhooks')
    ->name('api.v1.webhooks.')
    ->group(function () {
        // GET — Meta webhook subscription verification handshake.
        Route::get('whatsapp', [WhatsAppWebhookController::class, 'verify'])
            ->name('whatsapp.verify');

        // POST — Meta webhook event delivery (status updates, inbound messages).
        Route::post('whatsapp', [WhatsAppWebhookController::class, 'receive'])
            ->name('whatsapp.receive');
    });

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Versioned, API-key authenticated, rate-limited endpoints.
|
*/

Route::prefix('v1')
    ->middleware(['auth.apikey', 'throttle:api', 'log.api'])
    ->group(function () {
        Route::prefix('messages')
            ->controller(MessageController::class)
            ->group(function () {
                Route::post('send', 'store')
                    ->middleware('api.key.permission:messages.send')
                    ->name('api.v1.messages.store');
                Route::post('send-media', 'storeMedia')
                    ->middleware('api.key.permission:messages.send')
                    ->name('api.v1.messages.store-media');
                Route::get('{message}', 'show')
                    ->middleware('api.key.permission:messages.read')
                    ->name('api.v1.messages.show');
            });

        Route::prefix('otp')
            ->controller(OtpController::class)
            ->group(function () {
                Route::post('generate', 'generate')
                    ->middleware('api.key.permission:otp.generate')
                    ->name('api.v1.otp.generate');
                Route::post('verify', 'verify')
                    ->middleware('api.key.permission:otp.verify')
                    ->name('api.v1.otp.verify');
            });

        Route::prefix('templates')
            ->controller(TemplateController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->middleware('api.key.permission:templates.read')
                    ->name('api.v1.templates.index');
                Route::post('/', 'store')
                    ->middleware('api.key.permission:templates.create')
                    ->name('api.v1.templates.store');
                Route::get('{template}', 'show')
                    ->middleware('api.key.permission:templates.read')
                    ->name('api.v1.templates.show');
            });

        Route::prefix('contacts')
            ->controller(ContactController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->middleware('api.key.permission:contacts.read')
                    ->name('api.v1.contacts.index');
                Route::post('/', 'store')
                    ->middleware('api.key.permission:contacts.create')
                    ->name('api.v1.contacts.store');
            });

        Route::prefix('campaigns')
            ->controller(CampaignController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->middleware('api.key.permission:campaigns.read')
                    ->name('api.v1.campaigns.index');
                Route::post('/', 'store')
                    ->middleware('api.key.permission:campaigns.create')
                    ->name('api.v1.campaigns.store');
                Route::get('{campaign}', 'show')
                    ->middleware('api.key.permission:campaigns.read')
                    ->name('api.v1.campaigns.show');
                Route::put('{campaign}', 'update')
                    ->middleware('api.key.permission:campaigns.create')
                    ->name('api.v1.campaigns.update');
                Route::delete('{campaign}', 'destroy')
                    ->middleware('api.key.permission:campaigns.create')
                    ->name('api.v1.campaigns.destroy');
                Route::post('{campaign}/send', 'send')
                    ->middleware('api.key.permission:campaigns.send')
                    ->name('api.v1.campaigns.send');
                Route::get('{campaign}/recipients', 'recipients')
                    ->middleware('api.key.permission:campaigns.read')
                    ->name('api.v1.campaigns.recipients');
            });
    });
