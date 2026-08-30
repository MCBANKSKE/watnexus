<?php

namespace App\Services\Otp;

use App\Models\Company;
use App\Models\Contact;
use App\Models\OtpVerification;
use App\Models\WhatsAppPhoneNumber;
use App\Services\WhatsApp\Messaging\SendTemplateMessageService;
use Illuminate\Support\Facades\Hash;

/**
 * Generate a new one-time password and persist it securely.
 */
class GenerateOtpService
{
    public function __construct(
        protected SendTemplateMessageService $sendTemplateMessageService
    ) {}

    /**
     * Create an OTP verification record.
     *
     * @param  array<int, string>  $templateVariables  Values for the template body placeholders.
     * @return array{verification: OtpVerification, plain_code?: string}
     */
    public function handle(
        Company $company,
        string $phone,
        int $length = 6,
        int $expiryMinutes = 5,
        ?Contact $contact = null,
        ?WhatsAppPhoneNumber $phoneNumber = null,
        ?string $templateName = null,
        array $templateVariables = [],
        bool $exposePlainCode = false
    ): array {
        $code = $this->generateCode($length);

        $verification = OtpVerification::create([
            'company_id' => $company->id,
            'contact_id' => $contact?->id,
            'whatsapp_phone_number_id' => $phoneNumber?->id,
            'reference' => OtpVerification::generateReference(),
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'metadata' => [
                'length' => $length,
                'template' => $templateName,
            ],
        ]);

        // Optionally deliver the code immediately via a WhatsApp template.
        if ($templateName !== null && $phoneNumber !== null) {
            $this->sendTemplateMessageService->handle(
                $phoneNumber,
                $phone,
                $templateName,
                'en',
                $this->buildVariables($templateVariables, $code)
            );
        }

        $result = ['verification' => $verification];

        if ($exposePlainCode) {
            $result['plain_code'] = $code;
        }

        return $result;
    }

    /**
     * Generate a random numeric code.
     */
    protected function generateCode(int $length): string
    {
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }

        return $code;
    }

    /**
     * Build template "body" components with variables.
     *
     * @param  array<int, string>  $variables
     * @return array<int, array<string, mixed>>
     */
    protected function buildVariables(array $variables, string $code): array
    {
        if (empty($variables)) {
            $variables = [$code];
        }

        return [[
            'type' => 'body',
            'parameters' => array_map(
                fn (string $value): array => [
                    'type' => 'text',
                    'text' => $value,
                ],
                $variables
            ),
        ]];
    }
}
