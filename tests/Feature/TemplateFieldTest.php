<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\ApiKey\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateFieldTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsCompany(): array
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Test Co']);
        $user->companies()->attach($company, ['role' => 'admin', 'is_active' => true]);

        $service = app(ApiKeyService::class);
        $result = $service->generate(
            $company,
            $user,
            'Test Key',
            ['templates.create', 'templates.read']
        );

        return [$company, $result['plain_text_key']];
    }

    public function test_template_created_with_header_footer_buttons_variables(): void
    {
        [$company, $key] = $this->actingAsCompany();

        $payload = [
            'name' => 'welcome_msg',
            'language' => 'en',
            'category' => 'utility',
            'body' => 'Hello {{1}}, welcome!',
            'header' => ['type' => 'text', 'text' => 'Welcome'],
            'footer' => 'Powered by WatNexus',
            'buttons' => [
                ['type' => 'reply', 'text' => 'Get Started', 'value' => 'get_started'],
            ],
            'variables' => [
                ['key' => '1', 'value' => 'Customer'],
            ],
        ];

        $response = $this->withHeader('X-API-Key', $key)
            ->postJson('/api/v1/templates', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'welcome_msg',
                    'language' => 'en',
                    'category' => 'utility',
                    'body' => 'Hello {{1}}, welcome!',
                    'footer' => 'Powered by WatNexus',
                ],
            ]);

        $template = MessageTemplate::where('name', 'welcome_msg')->first();

        $this->assertNotNull($template);
        $this->assertSame(['type' => 'text', 'text' => 'Welcome'], $template->header);
        $this->assertSame([
            ['type' => 'reply', 'text' => 'Get Started', 'value' => 'get_started'],
        ], $template->buttons);
        $this->assertSame([['key' => '1', 'value' => 'Customer']], $template->variables);
    }

    public function test_template_show_returns_full_data(): void
    {
        [$company, $key] = $this->actingAsCompany();

        $template = MessageTemplate::create([
            'company_id' => $company->id,
            'name' => 'test_tpl',
            'language' => 'en',
            'category' => 'utility',
            'body' => 'Body text',
            'header' => ['type' => 'text', 'text' => 'Header'],
            'footer' => 'Footer text',
            'buttons' => [['type' => 'reply', 'text' => 'OK', 'value' => 'ok']],
            'variables' => [['key' => '1', 'value' => 'X']],
            'status' => 'draft',
        ]);

        $response = $this->withHeader('X-API-Key', $key)
            ->getJson("/api/v1/templates/{$template->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'test_tpl',
                    'header' => ['type' => 'text', 'text' => 'Header'],
                    'footer' => 'Footer text',
                ],
            ]);
    }
}
