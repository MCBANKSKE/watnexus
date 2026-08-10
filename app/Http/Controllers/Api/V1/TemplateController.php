<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreTemplateRequest;
use App\Models\MessageTemplate;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class TemplateController extends ApiController
{
    /**
     * List the company's WhatsApp message templates.
     */
    public function index(Request $request)
    {
        return ApiResponse::data(
            $this->company($request)->messageTemplates()->get()
        );
    }

    /**
     * Create a template as a local draft.
     */
    public function store(StoreTemplateRequest $request)
    {
        $data = $request->validated();

        $template = $this->company($request)->messageTemplates()->create([
            'name' => $data['name'],
            'language' => $data['language'],
            'category' => $data['category'],
            'body' => $data['body'],
            'header' => $data['header'] ?? null,
            'footer' => $data['footer'] ?? null,
            'buttons' => $data['buttons'] ?? null,
            'variables' => $data['variables'] ?? null,
            'status' => 'draft',
        ]);

        return ApiResponse::data(
            $template,
            'Template created as draft.',
            201
        );
    }

    /**
     * Show a single template that belongs to the company.
     */
    public function show(Request $request, MessageTemplate $template)
    {
        if ($template->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Template not found.', 404);
        }

        return ApiResponse::data($template);
    }
}