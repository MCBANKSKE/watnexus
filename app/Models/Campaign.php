<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'message_template_id',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'queued_count',
        'sent_count',
        'delivered_count',
        'read_count',
        'failed_count',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class)
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

        public function contactLists()
    {
        return $this->belongsToMany(ContactList::class)
            ->withTimestamps();
    }

    /**
     * Messages sent as part of this campaign.
     */
    public function messages()
    {
        return $this->belongsToMany(Message::class)
            ->withTimestamps();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }
}