<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
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
            'name' => ['required', 'string', 'regex:/^[a-z0-9_]+$/i', 'max:255'],
            'language' => ['required', 'string', 'size:2'],
            'category' => ['required', 'string', 'in:authentication,utility,marketing'],
            'body' => ['required', 'string', 'max:4096'],
            'header' => ['nullable', 'array'],
            'footer' => ['nullable', 'string', 'max:1024'],
            'buttons' => ['nullable', 'array'],
            'variables' => ['nullable', 'array'],
        ];
    }
}