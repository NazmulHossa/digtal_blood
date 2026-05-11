<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DonorBadge Model
 * ─────────────────
 * একজন ডোনারের gamification badge ও points।
 *
 * Badge Levels (points অনুযায়ী auto-upgrade):
 *   🥉 Bronze : 0   – 99   pts  (নতুন ডোনার)
 *   🥈 Silver : 100 – 299  pts
 *   🥇 Gold   : 300 – 599  pts
 *   🦸 Hero   : 600+       pts  (Community Hero)
 *
 * Points System:
 *   Registration:     +10 pts
 *   Profile complete: +20 pts
 *   Each donation:    +50 pts
 *   Every 3rd donation: +30 bonus pts
 */
class DonorBadge extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'total_points',
        'donation_count',
    ];

    // ── Relationship ──────────────────────────────────────────

    /**
     * এই badge যার — ডোনার।
     * ব্যবহার: $badge->user->name → "Rahim Uddin"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static Config ─────────────────────────────────────────

    /**
     * সব badge level-এর display config।
     * DashboardController ও Vue component-এ ব্যবহার হয়।
     *
     * @return array<string, array>
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
                'description' => "You've made a real difference!",
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

    // ── Computed Attributes ───────────────────────────────────

    /**
     * পরবর্তী level-এ কত পয়েন্ট লাগবে।
     * Hero-তে পৌঁছালে null।
     * ব্যবহার: $badge->next_level_points → 300
     */
    public function getNextLevelPointsAttribute(): ?int
    {
        $map = [
            'bronze' => 100,
            'silver' => 300,
            'gold'   => 600,
            'hero'   => null,
        ];
        return $map[$this->level] ?? null;
    }

    /**
     * Progress bar percentage (0–100)।
     * Vue-এর BadgeCard component progress bar-এ দেখানো হয়।
     * ব্যবহার: $badge->progress_percentage → 45
     */
    public function getProgressPercentageAttribute(): int
    {
        $levels  = self::levels();
        $current = $levels[$this->level];
        $nextMin = $this->next_level_points;

        if ($nextMin === null) {
            return 100; // Hero — max level
        }

        $range  = $nextMin - $current['min_points'];
        $earned = $this->total_points - $current['min_points'];

        return min(100, (int) round(($earned / $range) * 100));
    }

    /**
     * Current level-এর display config।
     * ব্যবহার: $badge->config['icon'] → "🥇"
     */
    public function getConfigAttribute(): array
    {
        return self::levels()[$this->level] ?? self::levels()['bronze'];
    }
}