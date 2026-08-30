<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    use \App\Models\Concerns\BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'is_active',
        'contacts_count',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Company that owns the list.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * User who created the list.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Contacts inside this list.
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_list_contact')
            ->withTimestamps();
    }

    /**
     * Check whether the list is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the campaigns using this contact list.
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class)
            ->withTimestamps();
    }
}
