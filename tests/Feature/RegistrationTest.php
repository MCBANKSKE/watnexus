<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('pending_company_setup', 'web');
        Role::findOrCreate('company_admin', 'web');
    }

    public function test_guest_can_register_and_is_sent_to_company_setup(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Founder',
            'email' => 'jane@example.test',
            'password' => 'supersecret1',
            'password_confirmation' => 'supersecret1',
        ]);

        $response->assertRedirect('/company-setup');

        $this->assertDatabaseHas('users', ['email' => 'jane@example.test']);

        $user = User::where('email', 'jane@example.test')->first();
        $this->assertTrue($user->hasRole('pending_company_setup'));
    }

    public function test_new_user_without_company_is_redirected_to_setup_from_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pending_company_setup');

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('company.setup.index'));
    }

    public function test_user_can_complete_company_setup(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pending_company_setup');

        $response = $this->actingAs($user)->post('/company-setup', [
            'name' => 'Acme WhatsApp Ltd',
            'email' => 'info@acme.test',
            'timezone' => 'Africa/Nairobi',
        ]);

        $response->assertRedirect('/admin');

        $company = Company::where('name', 'Acme WhatsApp Ltd')->first();
        $this->assertNotNull($company);
        $this->assertNotNull($company->slug);
        $this->assertEquals('active', $company->status);

        // User attached as active admin.
        $this->assertTrue($user->belongsToCompany($company));
        $this->assertEquals('admin', $user->roleIn($company));
        $this->assertTrue($user->isActiveIn($company));
        $this->assertTrue($user->hasRole('company_admin'));

        // Dashboard now accessible.
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_setup_validates_company_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/company-setup', [])
            ->assertSessionHasErrors(['name']);
    }
}
