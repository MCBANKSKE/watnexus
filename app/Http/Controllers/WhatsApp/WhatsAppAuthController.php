<?php

namespace App\Http\Controllers\WhatsApp;

use App\Filament\Resources\WhatsAppAccounts\WhatsAppAccountResource;
use App\Http\Controllers\Controller;
use App\Services\WhatsApp\Authentication\ConnectWhatsAppService;
use Illuminate\Http\Request;

class WhatsAppAuthController extends Controller
{
    public function __construct(
        protected ConnectWhatsAppService $connectWhatsApp
    ) {}

    /** Display the self-service WhatsApp connection options. */
    public function show()
    {
        return view('whatsapp.connect', [
            'manualUrl' => WhatsAppAccountResource::getUrl('create'),
            'metaConfigured' => filled(config('services.whatsapp.app_id')),
            'qrConfigured' => filled(config('services.whatsapp.oauth_config_id')),
        ]);
    }

    /** Start WhatsApp onboarding. */
    public function redirect(Request $request)
    {
        return redirect()->route('whatsapp.connect');
    }

    /**
     * Handle the callback from Meta.
     */
    public function callback(Request $request)
    {
        // We will use ConnectWhatsAppService here to store the
        // returned authorization information and connect the account.
    }
}
