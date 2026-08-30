<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GenerateOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'to' => ['required', 'string', 'min:6', 'max:20'],
            'phone_number_id' => ['nullable', 'integer'],
            'length' => ['nullable', 'integer', 'min:4', 'max:8'],
            'expires_in_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'variables' => ['nullable', 'array', 'max:15'],
            'variables.*' => ['string', 'max:255'],
        ];
    }
}
