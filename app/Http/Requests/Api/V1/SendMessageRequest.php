<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:4096'],
            'name' => ['nullable', 'string', 'max:255'],
            'wa_id' => ['nullable', 'string', 'max:32'],
            'preview_url' => ['nullable', 'boolean'],
        ];
    }
}