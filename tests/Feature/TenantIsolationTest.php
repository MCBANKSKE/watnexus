<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppPhoneNumber;
use App\Services\ApiKey\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('pending_company_setup', 'web');
        Role::findOrCreate('company_admin', 'web');
    }

    private function companyWithKey(string $name): array
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => $name]);
        $user->companies()->attach($company, ['role' => 'admin', 'is_active' => true]);

        $key = app(ApiKeyService::class)
            ->generate($company, $user, 'Key '.$name, ['*'])['plain_text_key'];

        return [$company, $key];
    }

    private function contactWithConversation(Company $company): array
    {
        $contact = Contact::create([
            'company_id' => $company->id,
            'phone' => '+254700000999',
            'status' => 'active',
        ]);

        $account = WhatsAppAccount::create([
            'company_id' => $company->id,
            'status' => 'connected',
        ]);

        $phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'phone_number_id' => 'pn_'.uniqid(),
            'phone_number' => '+254700001000',
            'status' => 'connected',
        ]);

        $conversation = Conversation::create([
            'company_id' => $company->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_id' => $contact->id,
            'status' => 'open',
        ]);

        return [$contact, $conversation];
    }

    public function test_company_a_cannot_read_company_b_message(): void
    {
        [$companyA, $keyA] = $this->companyWithKey('Company A');
        [$companyB] = $this->companyWithKey('Company B');

        [, $conversation] = $this->contactWithConversation($companyB);

        $message = Message::create([
            'company_id' => $companyB->id,
            'conversation_id' => $conversation->id,
            'whatsapp_phone_number_id' => $conversation->whatsapp_phone_number_id,
            'contact_id' => $conversation->contact_id,
            'direction' => 'outbound',
            'type' => 'text',
            'status' => 'queued',
            'body' => 'secret',
        ]);

        $this->withHeader('X-API-Key', $keyA)
            ->getJson("/api/v1/messages/{$message->id}")
            ->assertStatus(404);
    }

    public function test_company_a_cannot_read_company_b_campaign(): void
    {
        [$companyA, $keyA] = $this->companyWithKey('Company A');
        [$companyB] = $this->companyWithKey('Company B');

        $campaign = Campaign::create([
            'company_id' => $companyB->id,
            'name' => 'B campaign',
            'status' => 'draft',
        ]);

        $this->withHeader('X-API-Key', $keyA)
            ->getJson("/api/v1/campaigns/{$campaign->id}")
            ->assertStatus(404);
    }

    public function test_contact_creation_stamps_company_from_context(): void
    {
        [$companyA, $keyA] = $this->companyWithKey('Company A');

        $this->withHeader('X-API-Key', $keyA)
            ->postJson('/api/v1/contacts', [
                'phone' => '+254700000001',
                'name' => 'Test Contact',
            ])
            ->assertStatus(201);

        $contact = Contact::query()->where('phone', '+254700000001')->first();

        $this->assertNotNull($contact);
        $this->assertEquals($companyA->id, $contact->company_id);
    }

    public function test_company_a_cannot_see_company_b_templates(): void
    {
        [$companyA, $keyA] = $this->companyWithKey('Company A');
        [$companyB] = $this->companyWithKey('Company B');

        MessageTemplate::create([
            'company_id' => $companyB->id,
            'name' => 'b_only_tpl',
            'language' => 'en',
            'category' => 'utility',
            'body' => 'Hello',
            'status' => 'approved',
        ]);

        $response = $this->withHeader('X-API-Key', $keyA)
            ->getJson('/api/v1/templates');

        $response->assertStatus(200);

        $names = collect($response->json('data.data ?? []'))
            ->pluck('name')
            ->toArray();

        $this->assertNotContains('b_only_tpl', $names);
    }
}
