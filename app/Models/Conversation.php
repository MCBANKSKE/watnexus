<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use \App\Models\Concerns\BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'whatsapp_phone_number_id',
        'contact_id',
        'status',
        'last_message',
        'last_message_at',
        'last_message_direction',
        'unread_count',
        'assigned_to',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'metadata' => 'array',
        'pricing' => 'array',
    ];

    /**
     * Company that owns the conversation.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * WhatsApp number used for the conversation.
     */
    public function whatsappPhoneNumber()
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    /**
     * Customer involved in the conversation.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Company user/agent assigned to the conversation.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Messages belonging to this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function updateLastMessage(Message $message): void
    {
        $this->update([
            'last_message' => $message->body,
            'last_message_at' => $message->created_at ?? now(),
            'last_message_direction' => $message->direction,
        ]);
    }
}
