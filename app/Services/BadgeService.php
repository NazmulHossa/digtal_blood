<?php

namespace App\Services;

use App\Models\DonorBadge;
use App\Models\DonorPoint;
use App\Models\User;
use Carbon\Carbon;

/**
 * ============================================================
 * BadgeService — Gamification Engine
 * ============================================================
 *
 * ডোনারদের encourage করার জন্য badge system।
 * Points award করে, badges upgrade করে, donation timer track করে।
 *
 * Usage:
 *   // Registration-এ প্রথম পয়েন্ট দাও
 *   app(BadgeService::class)->awardRegistration($user);
 * ============================================================
 */
class BadgeService
{
    // ── Point Values ──────────────────────────────────────────
    // এক জায়গায় রাখা হয়েছে যাতে সহজে পরিবর্তন করা যায়।
    const POINTS_REGISTRATION     = 10;
    const POINTS_PROFILE_COMPLETE = 20;
    const POINTS_DONATION         = 50;
    const POINTS_STREAK_BONUS     = 30;

    // ── Badge Thresholds ──────────────────────────────────────
    const THRESHOLD_SILVER = 100;
    const THRESHOLD_GOLD   = 300;
    const THRESHOLD_HERO   = 600;

    /**
     * নতুন ইউজার register করলে প্রথম পয়েন্ট দাও।
     * একটি DonorBadge record তৈরি করো (bronze থেকে শুরু)।
     *
     * @param  User  $user
     */
    public function awardRegistration(User $user): void
    {
        // Badge record তৈরি করো (যদি না থাকে)
        DonorBadge::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 'bronze', 'total_points' => 0, 'donation_count' => 0]
        );

        // Registration পয়েন্ট দাও
        $this->addPoints($user, self::POINTS_REGISTRATION, 'registration', 'Welcome bonus');
    }

    /**
     * Profile সম্পূর্ণ হলে পয়েন্ট দাও।
     * Profile complete = blood_group, district, phone সব দেওয়া আছে।
     *
     * @param  User  $user
     */
    public function awardProfileCompletion(User $user): void
    {
        // Profile কি সত্যিই complete?
        $isComplete = $user->blood_group_id
                   && $user->district_id
                   && $user->phone;

        if (! $isComplete) return;

        // আগে কি এই পয়েন্ট দেওয়া হয়েছে? (duplicate check)
        $alreadyAwarded = DonorPoint::where('user_id', $user->id)
                                    ->where('reason', 'profile_complete')
                                    ->exists();

        if ($alreadyAwarded) return;

        $this->addPoints(
            $user,
            self::POINTS_PROFILE_COMPLETE,
            'profile_complete',
            'Profile fully completed'
        );
    }

    /**
     * Donation record হলে পয়েন্ট দাও।
     * প্রতি ৩তম donation-এ +30 bonus।
     *
     * @param  User  $user
     * @return array ['points_earned' => int, 'badge_upgraded' => bool, 'new_level' => string]
     */
    public function awardDonation(User $user): array
    {
        $badge = $user->badge ?? DonorBadge::create([
            'user_id' => $user->id,
            'level' => 'bronze',
            'total_points' => 0,
            'donation_count' => 0,
        ]);

        $pointsEarned = self::POINTS_DONATION;
        $newCount = $badge->donation_count + 1;

        // প্রতি ৩তম donation-এ bonus
        if ($newCount % 3 === 0) {
            $pointsEarned += self::POINTS_STREAK_BONUS;
        }

        // Points add করো
        $this->addPoints($user, $pointsEarned, 'donation', "Donation #{$newCount}");

        // Badge update করো
        $badge->increment('donation_count');
        $badge->increment('total_points', $pointsEarned);

        // Badge level check করো
        $oldLevel = $badge->level;
        $newLevel = $this->determineLevel($badge->total_points);
        $upgraded = $oldLevel !== $newLevel;

        if ($upgraded) {
            $badge->update(['level' => $newLevel]);
        }

        return [
            'points_earned' => $pointsEarned,
            'badge_upgraded' => $upgraded,
            'new_level' => $newLevel,
        ];
    }

    /**
     * Donation countdown timer data।
     * 90-দিনের মেডিক্যাল rule অনুযায়ী।
     *
     * @param  User  $user
     * @return array ['can_donate_now' => bool, 'days_remaining' => int|null, 'eligible_date' => string]
     */
    public function getDonationTimer(User $user): array
    {
        if (! $user->last_donation_date) {
            return [
                'can_donate_now' => true,
                'days_remaining' => null,
                'eligible_date' => null,
            ];
        }

        $eligible = $user->last_donation_date->addDays(90);
        $today = Carbon::today();
        $canDonate = $eligible <= $today;

        return [
            'can_donate_now' => $canDonate,
            'days_remaining' => $canDonate ? 0 : $today->diffInDays($eligible),
            'eligible_date' => $eligible->format('Y-m-d'),
        ];
    }

    /**
     * Points add করো transaction log-এ।
     * @param  User    $user
     * @param  int     $points
     * @param  string  $reason (registration, profile_complete, donation)
     * @param  string  $description
     */
    private function addPoints(User $user, int $points, string $reason, string $description): void
    {
        DonorPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
            'description' => $description,
        ]);
    }

    /**
     * Total points থেকে badge level decide করো।
     * @param  int  $totalPoints
     * @return string (bronze, silver, gold, hero)
     */
    private function determineLevel(int $totalPoints): string
    {
        if ($totalPoints >= self::THRESHOLD_HERO) {
            return 'hero';
        }
        if ($totalPoints >= self::THRESHOLD_GOLD) {
            return 'gold';
        }
        if ($totalPoints >= self::THRESHOLD_SILVER) {
            return 'silver';
        }
        return 'bronze';
    }
}
