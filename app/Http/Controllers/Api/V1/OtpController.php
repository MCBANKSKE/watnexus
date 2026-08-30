<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\GenerateOtpRequest;
use App\Http\Requests\Api\V1\VerifyOtpRequest;
use App\Models\Contact;
use App\Models\WhatsAppPhoneNumber;
use App\Services\Otp\GenerateOtpService;
use App\Services\Otp\VerifyOtpService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OtpController extends ApiController
{
    /**
     * Generate and deliver an OTP to a phone number.
     */
    public function generate(GenerateOtpRequest $request)
    {
        $data = $request->validated();

        $company = $this->company($request);

        $phoneNumber = $this->resolvePhoneNumber($request, $data);

        $contact = Contact::firstOrCreate(
            [
                'company_id' => $company->id,
                'phone' => $data['to'],
            ],
            ['status' => 'active']
        );

        $result = app(GenerateOtpService::class)->handle(
            $company,
            $data['to'],
            (int) ($data['length'] ?? 6),
            (int) ($data['expires_in_minutes'] ?? 5),
            $contact,
            $phoneNumber,
            $data['template_name'] ?? null,
            $data['variables'] ?? [],
            false
        );

        $verification = $result['verification'];

        return ApiResponse::data([
            'reference' => $verification->reference,
            'expires_at' => $verification->expires_at?->toIso8601String(),
        ], 'OTP generated.', 201);
    }

    /**
     * Verify a submitted OTP code.
     */
    public function verify(VerifyOtpRequest $request)
    {
        $valid = app(VerifyOtpService::class)->handle(
            $request->reference,
            $request->code
        );

        if (! $valid) {
            return ApiResponse::error(
                'Invalid, used or expired OTP.',
                400
            );
        }

        $this->company($request);

        return ApiResponse::message('OTP verified successfully.');
    }

    /**
     * Pick a connected phone number (specific ID within the company,
     * otherwise the company's first connected number).
     */
    protected function resolvePhoneNumber(
        Request $request,
        array $data
    ): ?WhatsAppPhoneNumber {
        $company = $this->company($request);

        $query = $company->whatsappPhoneNumbers()
            ->where('status', 'connected');

        return isset($data['phone_number_id'])
            ? $query->whereKey($data['phone_number_id'])->first()
            : $query->first();
    }
}
