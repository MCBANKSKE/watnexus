<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'reference_id',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
        'usage_date',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
        'usage_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Company consuming the service.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}