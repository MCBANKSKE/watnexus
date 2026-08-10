<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'key_prefix',
        'key_hash',
        'permissions',
        'is_active',
        'expires_at',
        'last_used_at',
        'allowed_ips',
        'metadata',
    ];

    protected $casts = [
        'permissions' => 'array',
        'allowed_ips' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Company that owns the API key.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * User who created the API key.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check whether the key is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check whether the key has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Check whether the API key can currently be used.
     */
    public function canBeUsed(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * Check whether the key has a particular permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Get the request logs for this API key.
     */
    public function requestLogs()
    {
        return $this->hasMany(ApiRequestLog::class);
    }
}