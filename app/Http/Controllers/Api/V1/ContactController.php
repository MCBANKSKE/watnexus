<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ContactController extends ApiController
{
    /**
     * List the company's contacts.
     */
    public function index(Request $request)
    {
        $contacts = $this->company($request)->contacts()
            ->with(['contactLists'])
            ->paginate(
                50,
                ['*'],
                'page',
                max((int) $request->query('page', 1), 1)
            )
            ->withQueryString();

        return ApiResponse::data($contacts);
    }

    /**
     * Create or update a contact (upsert on phone).
     */
    public function store(StoreContactRequest $request)
    {
        $data = $request->validated();

        $contact = $this->company($request)->contacts()->updateOrCreate(
            ['phone' => $data['phone']],
            [
                'name' => $data['name'] ?? null,
                'wa_id' => $data['wa_id'] ?? null,
                'email' => $data['email'] ?? null,
                'whatsapp_name' => $data['whatsapp_name'] ?? null,
                'country_code' => $data['country_code'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]
        );

        $status = $contact->wasRecentlyCreated ? 201 : 200;

        return ApiResponse::data(
            $contact,
            $contact->wasRecentlyCreated
                ? 'Contact created.'
                : 'Contact updated.',
            $status
        );
    }
}