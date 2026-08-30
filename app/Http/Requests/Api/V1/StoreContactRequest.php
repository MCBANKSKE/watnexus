<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'phone' => ['required', 'string', 'min:6', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'wa_id' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp_name' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'in:active,blocked,archived'],
        ];
    }
}
