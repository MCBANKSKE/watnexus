<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use \App\Models\Concerns\BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'whatsapp_template_id',
        'name',
        'category',
        'language',
        'status',
        'body',
        'header',
        'footer',
        'buttons',
        'variables',
        'rejection_reason',
        'synced_at',
        'metadata',
    ];

    protected $casts = [
        'header' => 'array',
        'buttons' => 'array',
        'variables' => 'array',
        'metadata' => 'array',
        'synced_at' => 'datetime',
    ];

    /**
     * Company that owns the template.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Messages sent using this template.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Campaigns using this template.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isUsable(): bool
    {
        return in_array($this->status, [
            'approved',
        ]);
    }
}
