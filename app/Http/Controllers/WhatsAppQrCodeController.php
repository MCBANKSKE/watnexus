<?php

namespace App\Http\Controllers;

use App\Services\WhatsApp\Authentication\GenerateQrCodeService;
use App\Services\WhatsApp\Authentication\VerifyQrCodeConnectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WhatsAppQrCodeController extends Controller
{
    public function __construct(
        private GenerateQrCodeService $generateQrCodeService,
        private VerifyQrCodeConnectionService $verifyQrCodeService
    ) {}

    /**
     * Generate QR code for WhatsApp embedded signup.
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $data = $this->generateQrCodeService->handle();
            
            // Store session data in cache for verification
            Cache::put('qr_session_' . $data['session_id'], [
                'code' => $data['code'],
                'expires_at' => $data['expires_at'],
            ], now()->addMinutes(15));

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check QR code connection status.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'code' => 'required|string',
        ]);

        try {
            $status = $this->verifyQrCodeService->checkStatus(
                $request->session_id,
                $request->code
            );

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle QR code callback from Meta.
     */
    public function callback(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'session_id' => 'required|string',
        ]);

        try {
            $company = auth()->user()->companies()->wherePivot('is_active', true)->first();
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active company found for user.',
                ], 400);
            }

            $account = $this->verifyQrCodeService->handle(
                $request->session_id,
                $request->code,
                $company
            );

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection not yet established. Please try again.',
                ]);
            }

            // Clear cache
            Cache::forget('qr_session_' . $request->session_id);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp account connected successfully!',
                'account_id' => $account->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
