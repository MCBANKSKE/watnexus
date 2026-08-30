<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use \App\Models\Concerns\BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'api_key_id',
        'method',
        'endpoint',
        'ip_address',
        'user_agent',
        'status_code',
        'response_time_ms',
        'request_data',
        'response_data',
        'error_message',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status_code >= 200
            && $this->status_code < 300;
    }

    public function isFailed(): bool
    {
        return $this->status_code >= 400;
    }
}
