<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'whatsapp_message_id',
        'status',
        'error_code',
        'error_message',
        'occurred_at',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Message whose status changed.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            'sent',
            'delivered',
            'read',
        ]);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}