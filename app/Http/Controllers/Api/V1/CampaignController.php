<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCampaignRequest;
use App\Http\Requests\Api\V1\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Services\Campaign\SendCampaignService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CampaignController extends ApiController
{
    /**
     * List the company's campaigns (with pagination).
     */
    public function index(Request $request)
    {
        $campaigns = $this->company($request)->campaigns()
            ->with(['messageTemplate', 'contactLists'])
            ->latest()
            ->paginate(
                25,
                ['*'],
                'page',
                max((int) $request->query('page', 1), 1)
            )
            ->withQueryString();

        return ApiResponse::data($campaigns);
    }

    /**
     * Create a new draft campaign.
     */
    public function store(StoreCampaignRequest $request)
    {
        $data = $request->validated();

        $campaign = DB::transaction(function () use ($request, $data) {
            $campaign = $this->company($request)->campaigns()->create([
                'created_by' => $this->apiKey($request)->created_by,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'message_template_id' => $data['message_template_id'],
                'status' => 'draft',
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'settings' => $data['settings'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            if (!empty($data['contact_ids'])) {
                $campaign->contacts()->attach($data['contact_ids']);
            }

            if (!empty($data['contact_list_ids'])) {
                $campaign->contactLists()->attach($data['contact_list_ids']);
            }

            return $campaign;
        });

        return ApiResponse::data(
            $campaign->load(['messageTemplate', 'contacts', 'contactLists']),
                        'Campaign created.',
            201
        );
    }

    /**
     * Show a single campaign (with statistics).
     */
    public function show(Request $request, Campaign $campaign)
    {
        if ($campaign->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Campaign not found.', 404);
        }

        return ApiResponse::data(
            $campaign->load(['messageTemplate', 'contacts', 'contactLists', 'messages'])
        );
    }

    /**
     * Update an existing draft/scheduled campaign.
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        if ($campaign->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Campaign not found.', 404);
        }

        if ($campaign->isRunning() || $campaign->isCompleted()) {
            return ApiResponse::error(
                'Cannot modify a campaign that has already started or been completed.',
                409
            );
        }

        $data = $request->validated();

        $campaign->update([
            'name' => $data['name'] ?? $campaign->name,
            'description' => $data['description'] ?? $campaign->description,
            'message_template_id' => $data['message_template_id']
                ?? $campaign->message_template_id,
            'scheduled_at' => $data['scheduled_at'] ?? $campaign->scheduled_at,
            'settings' => $data['settings']
                ? array_merge($campaign->settings ?? [], $data['settings'])
                : $campaign->settings,
            'metadata' => $data['metadata']
                ? array_merge($campaign->metadata ?? [], $data['metadata'])
                : $campaign->metadata,
        ]);

        if (!empty($data['contact_ids'])) {
            $campaign->contacts()->syncWithoutDetaching($data['contact_ids']);
        }

        if (!empty($data['contact_list_ids'])) {
            $campaign->contactLists()->syncWithoutDetaching($data['contact_list_ids']);
        }

        return ApiResponse::data(
            $campaign->fresh()->load(['messageTemplate', 'contacts', 'contactLists']),
                        'Campaign updated.'
        );
    }

    /**
     * Remove a draft or scheduled campaign.
     */
    public function destroy(Request $request, Campaign $campaign)
    {
        if ($campaign->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Campaign not found.', 404);
        }

        if ($campaign->isRunning()) {
            return ApiResponse::error(
                'Cannot delete a campaign that is currently running.',
                409
            );
        }

        $campaign->delete();

                return ApiResponse::message('Campaign deleted.');
    }

    /**
     * Execute / send a draft or scheduled campaign.
     */
    public function send(Request $request, Campaign $campaign, SendCampaignService $service)
    {
        if ($campaign->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Campaign not found.', 404);
        }

        try {
            $campaign = $service->handle($campaign);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 409);
        }

        return ApiResponse::data(
            $campaign->fresh()->load(['messageTemplate', 'contacts', 'contactLists']),
                        'Campaign dispatched.'
        );
    }

    /**
     * List the recipients of a campaign with their delivery status.
     */
    public function recipients(Request $request, Campaign $campaign)
    {
        if ($campaign->company_id !== $this->company($request)->id) {
            return ApiResponse::error('Campaign not found.', 404);
        }

        $recipients = $campaign->contacts()
            ->withPivot([
                'status',
                'message_id',
                'queued_at',
                'sent_at',
                'delivered_at',
                'read_at',
                'failed_at',
                'error_message',
            ])
            ->orderBy('id')
            ->paginate(
                50,
                ['*'],
                'page',
                max((int) $request->query('page', 1), 1)
            )
            ->withQueryString();

        return ApiResponse::data($recipients);
    }
}



