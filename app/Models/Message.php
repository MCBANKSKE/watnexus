<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use \App\Models\Concerns\BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'conversation_id',
        'whatsapp_phone_number_id',
        'contact_id',
        'message_template_id',
        'sender_id',
        'reply_to_id',
        'direction',
        'type',
        'status',
        'whatsapp_message_id',
        'body',
        'media_url',
        'media_type',
        'media_filename',
        'error_code',
        'error_message',
        'metadata',
        'queued_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function whatsappPhoneNumber()
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function otpVerification()
    {
        return $this->hasOne(OtpVerification::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class)
            ->withTimestamps();
    }

    public function statuses()
    {
        return $this->hasMany(MessageStatus::class);
    }

    protected static function booted(): void
    {
        static::created(function (Message $message) {
            $message->conversation?->updateLastMessage($message);
        });

        static::updated(function (Message $message) {
            if ($message->isDirty(['status', 'body'])) {
                $message->conversation?->updateLastMessage($message);
            }
        });
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
