<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmOwnerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_image',
        'id_verification_status',
        'applied_at',
        'verified_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Verification statuses
    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';

    /**
     * Get the user that owns this application
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if ID image is uploaded
     */
    public function hasIdImage(): bool
    {
        return !empty($this->id_image);
    }

    /**
     * Check if ID is verified
     */
    public function isVerified(): bool
    {
        return $this->id_verification_status === self::STATUS_VERIFIED;
    }

    /**
     * Check if ID is pending verification
     */
    public function isPending(): bool
    {
        return $this->id_verification_status === self::STATUS_PENDING;
    }

    /**
     * Scope to get pending applications
     */
    public function scopePending($query)
    {
        return $query->where('id_verification_status', self::STATUS_PENDING);
    }

    /**
     * Scope to get verified applications
     */
    public function scopeVerified($query)
    {
        return $query->where('id_verification_status', self::STATUS_VERIFIED);
    }

    /**
     * Scope to get applications with ID images
     */
    public function scopeWithIdImage($query)
    {
        return $query->whereNotNull('id_image');
    }
}