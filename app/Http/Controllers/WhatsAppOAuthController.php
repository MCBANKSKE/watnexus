<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\Authentication\OAuthConnectService;
use App\Services\WhatsApp\Authentication\OAuthCallbackService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class WhatsAppOAuthController extends Controller
{
    public function __construct(
        private OAuthConnectService $oauthConnectService,
        private OAuthCallbackService $oauthCallbackService
    ) {}

    /**
     * Redirect to Meta OAuth authorization page.
     */
    public function authorize(): JsonResponse
    {
        try {
            $company = auth()->user()?->companies()->wherePivot('is_active', true)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active company found for user.',
                ], 400);
            }

            // Company context is sealed inside the encrypted state so the
            // callback can resolve it without a web session.
            $state = $this->oauthConnectService->generateState($company->id);

            $authorizationUrl = $this->oauthConnectService->getAuthorizationUrl($state);

            return response()->json([
                'success' => true,
                'authorization_url' => $authorizationUrl,
                'state' => $state,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle OAuth callback from Meta.
     *
     * Meta redirects the user's browser here via GET with ?code=&state=
     * (or ?error=...&error_description=... when the user denies consent).
     * The company context is resolved from the encrypted state, so no
     * web session is required.
     */
    public function callback(Request $request): RedirectResponse|JsonResponse
    {
        $wantsJson = $request->wantsJson();
        $redirectUrl = rtrim(config('app.frontend_url', config('app.url', '/')), '/')
            . config('services.whatsapp.oauth_success_redirect', '/whatsapp/accounts');

        // User denied consent or Meta returned an error.
        if ($request->filled('error')) {
            $message = $request->input('error_description', 'OAuth authorization was denied.');

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 400)
                : redirect()->away($redirectUrl)->with('oauth_error', $message);
        }

        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            $payload = $this->oauthConnectService->resolveState($request->state);

            if (!$payload) {
                return $this->oauthFailure($wantsJson, $redirectUrl, 'Invalid or expired OAuth state. Possible CSRF attack.');
            }

            $company = \App\Models\Company::find($payload['company_id']);

            if (!$company) {
                return $this->oauthFailure($wantsJson, $redirectUrl, 'Company not found for this OAuth session.');
            }

            $account = $this->oauthCallbackService->handle($request->code, $company);

            return $wantsJson
                ? response()->json([
                    'success' => true,
                    'message' => 'WhatsApp account connected successfully via OAuth!',
                    'account_id' => $account->id,
                    'account_name' => $account->name,
                ])
                : redirect()->away($redirectUrl)->with('oauth_success', 'WhatsApp account connected successfully!');
        } catch (\Exception $e) {
            return $this->oauthFailure($wantsJson, $redirectUrl, $e->getMessage());
        }
    }

    /**
     * Uniform failure handling: JSON for API clients, redirect for browsers.
     */
    private function oauthFailure(bool $wantsJson, string $redirectUrl, string $message): RedirectResponse|JsonResponse
    {
        return $wantsJson
            ? response()->json(['success' => false, 'message' => $message], 400)
            : redirect()->away($redirectUrl)->with('oauth_error', $message);
    }

    /**
     * Refresh OAuth token for an existing account.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|integer',
        ]);

        try {
            $account = \App\Models\WhatsAppAccount::findOrFail($request->account_id);
            
            if ($account->connection_method !== 'oauth') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is not connected via OAuth.',
                ], 400);
            }

            $newToken = $this->oauthCallbackService->refreshToken($account);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
