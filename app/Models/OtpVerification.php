<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'contact_id',
        'whatsapp_phone_number_id',
        'message_id',
        'reference',
        'phone',
        'code_hash',
        'status',
        'attempts',
        'max_attempts',
        'expires_at',
        'verified_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'code_hash',
    ];

    /**
     * Company that owns the OTP request.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Customer receiving the OTP.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * WhatsApp number used to send the OTP.
     */
    public function whatsappPhoneNumber()
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    /**
     * Message used to deliver the OTP.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Generate a unique reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'otp_' . Str::lower(Str::random(24));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Set the OTP code securely.
     */
    public function setCode(string $code): void
    {
        $this->code_hash = Hash::make($code);
    }

    /**
     * Verify a submitted OTP code.
     */
    public function verifyCode(string $code): bool
    {
        if (! $this->canBeVerified()) {
            return false;
        }

        $this->increment('attempts');

        if (! Hash::check($code, $this->code_hash)) {
            if ($this->attempts >= $this->max_attempts) {
                $this->update([
                    'status' => 'failed',
                ]);
            }

            return false;
        }

        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Check whether the OTP can still be verified.
     */
    public function canBeVerified(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->expires_at->isPast()) {
            $this->update([
                'status' => 'expired',
            ]);

            return false;
        }

        if ($this->attempts >= $this->max_attempts) {
            $this->update([
                'status' => 'failed',
            ]);

            return false;
        }

        return true;
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}