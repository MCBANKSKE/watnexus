<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'price',
        'currency',
        'billing_interval',
        'messages_limit',
        'otp_limit',
        'api_requests_limit',
        'campaigns_limit',
        'contacts_limit',
        'users_limit',
        'whatsapp_numbers_limit',
        'features',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'messages_limit' => 'integer',
        'otp_limit' => 'integer',
        'api_requests_limit' => 'integer',
        'campaigns_limit' => 'integer',
        'contacts_limit' => 'integer',
        'users_limit' => 'integer',
        'whatsapp_numbers_limit' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Companies subscribed to this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }

    /**
     * Check whether the plan is currently available.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check whether a limit is unlimited.
     */
    public function isUnlimited(string $limit): bool
    {
        return $this->{$limit} === null;
    }

    /**
     * Get a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return (bool) data_get(
            $this->features ?? [],
            $feature,
            false
        );
    }
}
