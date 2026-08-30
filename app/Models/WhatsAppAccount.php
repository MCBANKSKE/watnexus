<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppAccount extends Model
{
    use \App\Models\Concerns\BelongsToCompany, HasFactory;

    protected $table = 'whatsapp_accounts';

    protected $fillable = [
        'company_id',
        'business_account_id',
        'name',
        'status',
        'access_token',
        'token_expires_at',
        'metadata',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'metadata' => 'array',
        'access_token' => 'encrypted',
    ];

    /**
     * The company that owns this WhatsApp account.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if the WhatsApp account is connected.
     */
    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    /**
     * Check if the WhatsApp account is disconnected.
     */
    public function isDisconnected(): bool
    {
        return $this->status === 'disconnected';
    }

    /**
     * Get the phone numbers belonging to this WhatsApp account.
     */
    public function phoneNumbers()
    {
        return $this->hasMany(WhatsAppPhoneNumber::class, 'whatsapp_account_id');
    }

    /**
     * Get the webhook events for this WhatsApp account.
     */
    public function webhookEvents()
    {
        return $this->hasMany(WebhookEvent::class, 'whatsapp_account_id');
    }
}
