<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppPhoneNumber extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_phone_numbers';

    protected $fillable = [
        'company_id',
        'whatsapp_account_id',
        'phone_number_id',
        'phone_number',
        'display_name',
        'status',
        'quality_rating',
        'messaging_limit',
        'country_code',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The WhatsApp Business Account.
     */
    public function whatsappAccount()
    {
        return $this->belongsTo(
            WhatsAppAccount::class,
            'whatsapp_account_id'
        );
    }

    /**
     * Get the company that owns this phone number.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check whether the number is connected.
     */
    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    /**
     * Get the conversations using this phone number.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'whatsapp_phone_number_id');
    }

    /**
     * Get the messages sent/received via this phone number.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'whatsapp_phone_number_id');
    }

    /**
     * Get the webhook events for this phone number.
     */
    public function webhookEvents()
    {
        return $this->hasMany(WebhookEvent::class, 'whatsapp_phone_number_id');
    }

    /**
     * Get the OTP verifications for this phone number.
     */
    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class, 'whatsapp_phone_number_id');
    }
}