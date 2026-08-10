<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageMediaRequest extends FormRequest
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
            'to' => ['required', 'string', 'min:6', 'max:20'],
            'type' => ['required', 'string', 'in:image,video,audio,document,sticker'],
            'media_url' => ['required', 'url', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:3000'],
            'name' => ['nullable', 'string', 'max:255'],
            'wa_id' => ['nullable', 'string', 'max:32'],
        ];
    }
}