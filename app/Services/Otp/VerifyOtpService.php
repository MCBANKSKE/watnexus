<?php

namespace App\Services\Otp;

use App\Models\OtpVerification;

/**
 * Verify a submitted OTP code against a stored verification.
 */
class VerifyOtpService
{
    /**
     * Look up the verification by reference and validate the code.
     */
    public function handle(string $reference, string $code): bool
    {
        $verification = OtpVerification::query()
            ->where('reference', $reference)
            ->first();

        if (! $verification) {
            return false;
        }

        return $verification->verifyCode($code);
    }
}
