<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'website',
        'registration_number',
        'tax_number',
        'logo',
        'address',
        'city_id',
        'country_id',
        'status',
        'timezone',
        'currency_id',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Automatically generate a slug when creating a company.
     */
    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if (empty($company->slug)) {
                $company->slug = static::generateUniqueSlug($company->name);
            }
        });
    }

    /**
     * Generate a unique company slug.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check whether the company is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check whether the company is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get the country associated with the company.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get the currency associated with the company.
     */
    public function currency()
    {
        return $this->belongsTo(Country::class, 'currency_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'role',
                'is_active',
            ])
            ->withTimestamps();
    }

    /**
     * Get the WhatsApp accounts belonging to the company.
     */
    public function whatsappAccounts()
    {
        return $this->hasMany(WhatsAppAccount::class);
    }

    /**
     * Get the WhatsApp phone numbers belonging to the company.
     */
    public function whatsappPhoneNumbers()
    {
        return $this->hasMany(WhatsAppPhoneNumber::class);
    }

    /**
     * Get the contacts belonging to the company.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the message templates belonging to the company.
     */
    public function messageTemplates()
    {
        return $this->hasMany(MessageTemplate::class);
    }

    /**
     * Get the conversations belonging to the company.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the messages belonging to the company.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the API keys belonging to the company.
     */
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Get the webhook events belonging to the company.
     */
    public function webhookEvents()
    {
        return $this->hasMany(WebhookEvent::class);
    }

    /**
     * Get the API request logs belonging to the company.
     */
    public function apiRequestLogs()
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    /**
     * Get the OTP verifications belonging to the company.
     */
    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    /**
     * Get the campaigns belonging to the company.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the contact lists belonging to the company.
     */
    public function contactLists()
    {
        return $this->hasMany(ContactList::class);
    }

    /**
     * Get the usage records belonging to the company.
     */
    public function usageRecords()
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Get the subscriptions belonging to the company.
     */
    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }

    /**
     * Get the company's current active subscription.
     */
    public function activeSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->whereIn('status', [
                'active',
                'trialing',
            ])
            ->latestOfMany();
    }
}
