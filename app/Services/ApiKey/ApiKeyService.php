<?php

namespace App\Services\ApiKey;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Generate, validate, rotate and revoke company API keys.
 */
class ApiKeyService
{
    protected const PREFIX_LENGTH = 12;

    protected const SECRET_LENGTH = 32;

    /**
     * @var list<string>
     */
    protected const ALLOWED_PERMISSIONS = [
        '*',
        'messages.send',
        'messages.read',
        'otp.generate',
        'otp.verify',
        'templates.create',
        'templates.read',
        'contacts.create',
        'contacts.read',
        'campaigns.create',
        'campaigns.read',
        'campaigns.send',
    ];

    /**
     * Create a new API key for a company.
     *
     * @param  array<int, string>  $permissions
     * @param  array<int, string>  $allowedIps
     * @param  array<string, mixed>  $metadata
     * @return array{api_key: ApiKey, plain_text_key: string}
     */
    public function generate(
        Company $company,
        User $creator,
        string $name,
        array $permissions = [],
        ?int $expiresInDays = null,
        array $allowedIps = [],
        array $metadata = []
    ): array {
        $this->assertValidPermissions($permissions);

        $prefix = $this->randomPrefix();
        $secret = $this->randomSecret();

        $apiKey = ApiKey::create([
            'company_id' => $company->id,
            'created_by' => $creator->id,
            'name' => $name,
            'key_prefix' => $prefix,
            'key_hash' => $this->hashSecret($secret),
            'permissions' => $permissions ?: ['*'],
            'is_active' => true,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
            'allowed_ips' => $allowedIps ?: null,
            'metadata' => $metadata,
        ]);

        $plainTextKey = $this->format($prefix, $secret);

        return [
            'api_key' => $apiKey,
            'plain_text_key' => $plainTextKey,
        ];
    }

    /**
     * Validate a plain text key and return the matching active ApiKey.
     *
     * The full secret is never stored; we compare salted SHA-256 hashes.
     */
    public function resolve(?string $plainTextKey): ?ApiKey
    {
        if (! $plainTextKey) {
            return null;
        }

        $parts = explode('.', $plainTextKey, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }

        [$prefix, $secret] = $parts;

        $apiKey = ApiKey::query()
            ->with('company')
            ->where('key_prefix', $prefix)
            ->first();

        if (! $apiKey) {
            return null;
        }

        if (! $apiKey->canBeUsed()) {
            return null;
        }

        if (! hash_equals($apiKey->key_hash, $this->hashSecret($secret))) {
            return null;
        }

        return $apiKey;
    }

    /**
     * Generate a new secret for an existing key (keeps the prefix).
     *
     * @return array{api_key: ApiKey, plain_text_key: string}
     */
    public function rotate(ApiKey $apiKey): array
    {
        $secret = $this->randomSecret();

        $apiKey->update([
            'key_hash' => $this->hashSecret($secret),
        ]);

        return [
            'api_key' => $apiKey->fresh(),
            'plain_text_key' => $this->format($apiKey->key_prefix, $secret),
        ];
    }

    /**
     * Deactivate a key.
     */
    public function revoke(ApiKey $apiKey): bool
    {
        return $apiKey->update(['is_active' => false]);
    }

    /**
     * Reactivate a key.
     */
    public function restore(ApiKey $apiKey): bool
    {
        return $apiKey->update(['is_active' => true]);
    }

    /**
     * Record the last time the key was used.
     */
    public function touchLastUsed(ApiKey $apiKey): void
    {
        $apiKey->update(['last_used_at' => now()]);
    }

    /**
     * Check whether the request IP passes the key's IP restrictions.
     */
    public function isIpAllowed(ApiKey $apiKey, ?string $ip): bool
    {
        if (empty($apiKey->allowed_ips)) {
            return true;
        }

        return in_array($ip, $apiKey->allowed_ips, true);
    }

    /**
     * Check whether the key may perform a given action.
     */
    public function hasPermission(ApiKey $apiKey, ?string $permission): bool
    {
        if ($permission === null) {
            return true;
        }

        $permissions = $apiKey->permissions ?? [];

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * Assemble the plain text key: {prefix}.{secret}
     */
    protected function format(string $prefix, string $secret): string
    {
        return $prefix.'.'.$secret;
    }

    /**
     * One-way hash of the secret used for lookup validation.
     */
    protected function hashSecret(string $secret): string
    {
        return hash('sha256', $secret);
    }

    protected function randomPrefix(): string
    {
        return 'wax_'.Str::random(self::PREFIX_LENGTH);
    }

    protected function randomSecret(): string
    {
        return Str::random(self::SECRET_LENGTH);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    protected function assertValidPermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if (! in_array($permission, self::ALLOWED_PERMISSIONS, true)) {
                throw new InvalidArgumentException(
                    "Invalid API key permission: {$permission}"
                );
            }
        }
    }
}
