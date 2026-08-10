<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'next_billing_at',
        'price',
        'currency',
        'billing_interval',
        'provider',
        'provider_subscription_id',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'price' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Company owning this subscription.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Plan attached to this subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Is the subscription active?
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Is the company currently trialing?
     */
    public function isTrialing(): bool
    {
        return $this->status === 'trialing'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Is the subscription expired?
     */
    public function isExpired(): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->isPast();
    }

    /**
     * Can the company use the platform?
     */
    public function canUsePlatform(): bool
    {
        if ($this->status === 'active') {
            return true;
        }

        return $this->isTrialing();
    }
}