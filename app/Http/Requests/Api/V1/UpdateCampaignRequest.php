<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'message_template_id' => [
                'nullable',
                'integer',
                Rule::exists('message_templates', 'id'),
            ],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer', Rule::exists('contacts', 'id')],
            'contact_list_ids' => ['nullable', 'array'],
            'contact_list_ids.*' => ['integer', Rule::exists('contact_lists', 'id')],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'settings' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
