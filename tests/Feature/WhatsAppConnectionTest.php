<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\WhatsAppAccount;
use App\Services\WhatsApp\Authentication\ConnectWhatsAppService;
use App\Services\WhatsApp\Authentication\SyncWhatsAppPhoneNumbersService;
use App\Services\WhatsApp\Authentication\TestWhatsAppConnectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_connection_details_are_persisted_and_the_token_is_encrypted(): void
    {
        $company = Company::create(['name' => 'Acme Ltd']);

        $account = app(ConnectWhatsAppService::class)->handle(
            $company,
            'waba-123',
            'sensitive-system-user-token',
            'Acme WhatsApp',
            null,
            ['source' => 'manual']
        );

        $this->assertSame('connected', $account->status);
        $this->assertSame('sensitive-system-user-token', $account->access_token);
        $this->assertStringNotContainsString(
            'sensitive-system-user-token',
            (string) WhatsAppAccount::query()->value('access_token')
        );

        $updated = app(ConnectWhatsAppService::class)->handle(
            $company,
            'waba-123',
            'replacement-token',
            'Acme WhatsApp'
        );

        $this->assertSame($account->id, $updated->id);
        $this->assertSame(1, WhatsAppAccount::count());
        $this->assertSame('replacement-token', $updated->access_token);
    }

    public function test_valid_meta_connection_is_checked_and_phone_numbers_are_synced(): void
    {
        config()->set('services.whatsapp.api_url', 'https://graph.example.test');
        config()->set('services.whatsapp.graph_version', 'v1.0');

        $company = Company::create(['name' => 'Acme Ltd']);
        $account = WhatsAppAccount::create([
            'company_id' => $company->id,
            'business_account_id' => 'waba-123',
            'name' => 'Acme WhatsApp',
            'status' => 'pending',
            'connection_method' => 'manual',
            'access_token' => 'system-user-token',
        ]);

        Http::fake([
            'https://graph.example.test/v1.0/waba-123' => Http::response(['id' => 'waba-123']),
            'https://graph.example.test/v1.0/waba-123/phone_numbers' => Http::response([
                'data' => [[
                    'id' => 'phone-123',
                    'display_phone_number' => '+15551234567',
                    'verified_name' => 'Acme',
                    'quality_rating' => 'GREEN',
                ]],
            ]),
        ]);

        $this->assertTrue(app(TestWhatsAppConnectionService::class)->handle($account));

        $numbers = app(SyncWhatsAppPhoneNumbersService::class)->handle($account);

        $this->assertCount(1, $numbers);
        $this->assertDatabaseHas('whatsapp_phone_numbers', [
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'phone_number_id' => 'phone-123',
            'phone_number' => '+15551234567',
            'status' => 'connected',
        ]);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer system-user-token'));
    }
}
