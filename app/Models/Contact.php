<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'wa_id',
        'email',
        'whatsapp_name',
        'status',
        'country_code',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the conversations involving this contact.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the messages involving this contact.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the OTP verifications for this contact.
     */
    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    /**
     * Get the campaigns this contact is part of.
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class)
            ->withPivot([
                'status',
                'message_id',
                'queued_at',
                'sent_at',
                'delivered_at',
                'read_at',
                'failed_at',
                'error_message',
            ])
            ->withTimestamps();
    }

    /**
     * Get the contact lists this contact belongs to.
     */
    public function contactLists()
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_contact')
            ->withTimestamps();
    }
}