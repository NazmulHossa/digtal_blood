<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DonorPoint Model
 * ────────────────
 * প্রতিটি পয়েন্ট অর্জনের transaction log রাখে।
 *
 * reason values:
 *   'registration'     → +10
 *   'profile_complete' → +20
 *   'donation'         → +50
 *   'streak_bonus'     → +30
 */
class DonorPoint extends Model
{
    protected $table = 'donor_points';

    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'registration'     => '✅ Joined Platform',
            'profile_complete' => '📝 Completed Profile',
            'donation'         => '🩸 Blood Donated',
            'streak_bonus'     => '🔥 Streak Bonus',
            default            => $this->reason,
        };
    }
}
