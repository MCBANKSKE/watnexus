<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'whatsapp_account_id',
        'whatsapp_phone_number_id',
        'event_type',
        'event_id',
        'status',
        'attempts',
        'payload',
        'error_message',
        'processed_at',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsAppAccount::class);
    }

    public function whatsappPhoneNumber()
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}