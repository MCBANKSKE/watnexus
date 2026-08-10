<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\CustomResetPassword;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_superadmin',  
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            
        ];
    }

    /**
     * Check if this user is a system super admin (platform-level, no company dependency).
     */
    public function isSystemSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if this user is a company admin (can manage staff roles/permissions within their company).
     */
    public function isCompanyAdmin(): bool
    {
        return (bool) $this->is_superadmin;
    }

    /**
     * Determine if the user has unrestricted platform-level access.
     * Backwards-compatible alias for isSystemSuperAdmin().
     */
    public function isSuperAdmin(): bool
    {
        return $this->isSystemSuperAdmin();
    }

    /**
     * Get the companies this user belongs to.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot([
                'role',
                'is_active',
            ])
            ->withTimestamps();
    }

    /**
     * Get the user's role within a given company.
     */
    public function roleIn(Company $company): ?string
    {
        return $this->companies()
            ->where('companies.id', $company->getKey())
            ->value('company_user.role');
    }

    /**
     * Determine whether the user is active in a given company.
     */
    public function isActiveIn(Company $company): bool
    {
        return (bool) $this->companies()
            ->where('companies.id', $company->getKey())
            ->value('company_user.is_active');
    }

    /**
     * Determine whether the user belongs to the given company.
     */
    public function belongsToCompany(Company $company): bool
    {
        return $this->companies()
            ->where('companies.id', $company->getKey())
            ->exists();
    }

    /**
     * Attach the user to a company with an optional role.
     */
    public function attachToCompany(Company $company, ?string $role = null): void
    {
        $this->companies()->attach($company, [
            'role' => $role ?? 'member',
            'is_active' => true,
        ]);
    }

    /**
     * Update the user's role within a company.
     */
    public function updateCompanyRole(Company $company, string $role): bool
    {
        return $this->companies()
            ->where('companies.id', $company->getKey())
            ->updateExistingPivot($company->getKey(), [
                'role' => $role,
            ]);
    }

    /**
     * Get the API keys created by this user.
     */
    public function createdApiKeys()
    {
        return $this->hasMany(ApiKey::class, 'created_by');
    }

    /**
     * Get the campaigns created by this user.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'created_by');
    }

    /**
     * Get the contact lists created by this user.
     */
    public function contactLists()
    {
        return $this->hasMany(ContactList::class, 'created_by');
    }

}
