<?php
// ============================================================
// app/Models/DonorBadge.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DonorBadge Model
 * ─────────────────
 * একজন ডোনারের গ্যামিফিকেশন প্রোফাইল।
 * Badge level পয়েন্ট অনুযায়ী স্বয়ংক্রিয়ভাবে আপগ্রেড হয়।
 *
 * Point Thresholds:
 *   Bronze: 0   – 99   pts  🥉
 *   Silver: 100 – 299  pts  🥈
 *   Gold:   300 – 599  pts  🥇
 *   Hero:   600+       pts  🦸
 *
 * Points Earned:
 *   Registration:     +10
 *   Profile complete: +20
 *   Each donation:    +50
 *   Streak (3x):      +30 bonus
 */
class DonorBadge extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'total_points',
        'donation_count',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Badge Configuration (static data) ─────────────────────

    /**
     * সব badge-এর threshold, রঙ, icon, নাম এক জায়গায়।
     * BadgeService এই data ব্যবহার করে।
     */
    public static function levels(): array
    {
        return [
            'bronze' => [
                'label'       => 'Bronze Donor',
                'icon'        => '🥉',
                'min_points'  => 0,
                'color'       => '#CD7F32',
                'bg'          => '#FDF3E7',
                'description' => 'Welcome to the community!',
            ],
            'silver' => [
                'label'       => 'Silver Donor',
                'icon'        => '🥈',
                'min_points'  => 100,
                'color'       => '#9CA3AF',
                'bg'          => '#F3F4F6',
                'description' => 'You\'ve made a real difference!',
            ],
            'gold' => [
                'label'       => 'Gold Donor',
                'icon'        => '🥇',
                'min_points'  => 300,
                'color'       => '#D97706',
                'bg'          => '#FFFBEB',
                'description' => 'Outstanding commitment to saving lives!',
            ],
            'hero' => [
                'label'       => 'Community Hero',
                'icon'        => '🦸',
                'min_points'  => 600,
                'color'       => '#BC0000',
                'bg'          => '#FEF2F2',
                'description' => 'A true hero of Chittagong!',
            ],
        ];
    }

    /**
     * এই badge-এর পরবর্তী level কত পয়েন্টে।
     * Progress bar-এর জন্য ব্যবহার হয়।
     */
    public function getNextLevelPointsAttribute(): ?int
    {
        $thresholds = [0 => 100, 100 => 300, 300 => 600, 600 => null];

        foreach ($thresholds as $min => $next) {
            if ($this->total_points < ($next ?? PHP_INT_MAX)) {
                return $next;
            }
        }
        return null; // Hero level — max reached
    }

    /**
     * Progress bar percentage towards next level.
     * e.g., 150 pts → Silver (100–299) → (50/200) = 25%
     */
    public function getProgressPercentageAttribute(): int
    {
        $levels = self::levels();
        $current = $levels[$this->level];
        $nextMin = $this->next_level_points;

        if ($nextMin === null) return 100; // Max level

        $range   = $nextMin - $current['min_points'];
        $earned  = $this->total_points - $current['min_points'];

        return min(100, (int) round(($earned / $range) * 100));
    }

    /**
     * Badge-এর display config (icon, color, label).
     */
    public function getConfigAttribute(): array
    {
        return self::levels()[$this->level];
    }
}


// ============================================================
// app/Models/DonorPoint.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DonorPoint Model
 * ─────────────────
 * প্রতিটি পয়েন্ট transaction-এর record।
 * Audit trail হিসেবে কাজ করে।
 */
class DonorPoint extends Model
{
    protected $fillable = ['user_id', 'points', 'reason', 'note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable reason label.
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'donation'         => '🩸 Blood Donated',
            'registration'     => '✅ Joined Platform',
            'profile_complete' => '📝 Completed Profile',
            'streak_bonus'     => '🔥 Streak Bonus',
            default            => $this->reason,
        };
    }
}


// ============================================================
// app/Models/BloodConnection.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BloodConnection Model
 * ──────────────────────
 * Phone Masking system-এর মূল model।
 *
 * একজন recipient যখন ডোনারের profile-এ "Request to Connect"
 * বাটন চাপে, এই model-এ একটি record তৈরি হয়।
 *
 * Donor accept করলে উভয়েই phone number দেখতে পারে।
 *
 * Status:
 *   pending  → Donor এখনো দেখেনি / respond করেনি
 *   accepted → Donor রাজি হয়েছে, phone share হবে
 *   declined → Donor রাজি হননি
 */
class BloodConnection extends Model
{
    protected $fillable = [
        'requester_id',
        'donor_id',
        'blood_request_id',
        'status',
        'message',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    /** রিকোয়েস্টকারী (recipient) */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /** ডোনার */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    /** সম্পর্কিত blood request (যদি থাকে) */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    // ── Query Scopes ──────────────────────────────────────────

    /** শুধু pending connections */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** শুধু accepted connections */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}