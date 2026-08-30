<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\Authentication\OAuthConnectService;
use App\Services\WhatsApp\Authentication\OAuthCallbackService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

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
            $state = $this->oauthConnectService->generateState();
            
            // Store state in cache for validation
            Cache::put('oauth_state_' . $state, true, now()->addMinutes(10));

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
     */
    public function callback(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            // Validate state to prevent CSRF
            $cachedState = Cache::get('oauth_state_' . $request->state);
            
            if (!$cachedState || !$this->oauthConnectService->validateState($request->state, $request->state)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid state parameter. Possible CSRF attack.',
                ], 400);
            }

            // Clear state from cache
            Cache::forget('oauth_state_' . $request->state);

            $company = auth()->user()->companies()->wherePivot('is_active', true)->first();
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active company found for user.',
                ], 400);
            }

            $account = $this->oauthCallbackService->handle($request->code, $company);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp account connected successfully via OAuth!',
                'account_id' => $account->id,
                'account_name' => $account->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
