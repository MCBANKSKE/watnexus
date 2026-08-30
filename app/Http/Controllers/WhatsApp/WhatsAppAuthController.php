<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\Authentication\ConnectWhatsAppService;
use Illuminate\Http\Request;

class WhatsAppAuthController extends Controller
{
    public function __construct(
        protected ConnectWhatsAppService $connectWhatsApp
    ) {}

    /**
     * Start WhatsApp onboarding.
     */
    public function redirect(Request $request)
    {
        // We will implement Meta Embedded Signup here.
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
