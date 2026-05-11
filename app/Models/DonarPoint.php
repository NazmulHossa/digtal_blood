<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DonorPoint Model
 * ─────────────────
 * প্রতিটি পয়েন্ট transaction-এর log।
 *
 * কেন আলাদা table?
 *   - Audit trail: কখন কোন কারণে পয়েন্ট পেয়েছে সব record থাকে
 *   - Fraud prevention: duplicate award check করা যায়
 *   - History page-এ দেখানো যায়
 *
 * reason values:
 *   'registration'     → নতুন account তৈরি    (+10)
 *   'profile_complete' → profile পূরণ         (+20)
 *   'donation'         → রক্তদান              (+50)
 *   'streak_bonus'     → ৩ বার donation bonus (+30)
 */
class DonorPoint extends Model
{
    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'note',
    ];

    // ── Relationship ──────────────────────────────────────────

    /**
     * কোন ডোনারের পয়েন্ট।
     * ব্যবহার: $point->user->name → "Rahim Uddin"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessor ──────────────────────────────────────────────

    /**
     * Human-readable reason label।
     * Dashboard history-এ দেখানোর জন্য।
     * ব্যবহার: $point->reason_label → "🩸 Blood Donated"
     */
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