<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use App\Services\ApiKey\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsCompany(?string $permission = '*'): array
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Co']);
        $user->companies()->attach($company, ['role' => 'admin', 'is_active' => true]);

        $service = app(ApiKeyService::class);
        $result = $service->generate(
            $company,
            $user,
            'Test Key',
            $permission === '*' ? ['*'] : [$permission],
            null,
            [],
            []
        );

        return [$company, $result['plain_text_key']];
    }

    public function test_missing_api_key_returns_401(): void
    {
        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_wildcard_permission_allows_access(): void
    {
        [$company, $key] = $this->actingAsCompany();

        $response = $this->withHeader('X-API-Key', $key)
            ->getJson('/api/v1/campaigns');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_campaigns_send_permission_is_required(): void
    {
        [$company, $key] = $this->actingAsCompany('campaigns.send');

        $response = $this->withHeader('X-API-Key', $key)
            ->postJson('/api/v1/campaigns/1/send');

        // Should NOT be 403 — the permission should be recognized
        $response->assertStatus(404); // 404 = campaign 1 doesn't exist, permission passed
    }

    public function test_missing_campaigns_send_permission_returns_403(): void
    {
        [$company, $key] = $this->actingAsCompany('campaigns.read');

        $service = app(\App\Services\ApiKey\ApiKeyService::class);
        $resolved = $service->resolve($key);

        // Direct test of hasPermission method
        $hasPermission = $service->hasPermission($resolved, 'campaigns.send');
        \Log::info('hasPermission result: ' . ($hasPermission ? 'true' : 'false'));
        \Log::info('Key permissions: ' . json_encode($resolved?->permissions));

        $response = $this->withHeader('X-API-Key', $key)
            ->postJson('/api/v1/campaigns/1/send');

        \Log::info('Response status: ' . $response->getStatusCode());
        \Log::info('Response content: ' . $response->getContent());

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_api_key_service_validates_permissions_on_generation(): void
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Co']);
        $user->companies()->attach($company, ['role' => 'admin', 'is_active' => true]);

        $service = app(ApiKeyService::class);

        $this->expectNotToPerformAssertions();

        // 'campaigns.send' is a valid permission — should not throw
        $service->generate($company, $user, 'Valid', ['campaigns.send']);
    }

    public function test_inactive_api_key_is_rejected(): void
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Co']);
        $user->companies()->attach($company, ['role' => 'admin', 'is_active' => true]);

        $service = app(ApiKeyService::class);
        $result = $service->generate($company, $user, 'Test Key', ['*']);
        $apiKey = $result['api_key'];
        $apiKey->update(['is_active' => false]);

        $response = $this->withHeader('X-API-Key', $result['plain_text_key'])
            ->getJson('/api/v1/campaigns');

        $response->assertStatus(401);
    }
}
