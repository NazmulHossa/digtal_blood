<?php

namespace App\Services;

use App\Models\DonorBadge;
use App\Models\DonorPoint;
use App\Models\User;
use Carbon\Carbon;

/**
 * ============================================================
 * BadgeService — Gamification & Donation Tracking
 * ============================================================
 *
 * এই service ডোনারদের points ও badges manage করে।
 *
 * কেন আলাদা service?
 * ───────────────────
 * সব logic এক জায়গায় রাখলে:
 *   ✅ DashboardController clean থাকে
 *   ✅ Testing করা সহজ হয়
 *   ✅ Logic পরিবর্তন করলে এক জায়গায় করলেই হয়
 *
 * কখন points দেওয়া হয়?
 * ──────────────────
 * 1. Registration: +10 pts (নতুন ডোনার sign up করলে)
 * 2. Profile complete: +20 pts (সব তথ্য দিলে)
 * 3. Donation: +50 pts (blood donation record করলে)
 * 4. Streak bonus: +30 pts (প্রতি ৩তম donation)
 *
 * Badge Levels:
 * ────────────
 *   🥉 Bronze : 0–99 pts     (Default)
 *   🥈 Silver : 100–299 pts  (committed donor)
 *   🥇 Gold   : 300–599 pts  (experienced donor)
 *   🦸 Hero   : 600+ pts     (community hero)
 *
 * Usage in DashboardController:
 * ──────────────────────────────
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
     * Donation record করলে পয়েন্ট দাও।
     * এবং প্রতি ৩তম donation-এ bonus দাও।
     *
     * @param  User  $user
     * @return array ['points_earned' => int, 'badge_upgraded' => bool, 'new_level' => string|null]
     */
    public function awardDonation(User $user): array
    {
        $badge = $user->badge ?? DonorBadge::create([
            'user_id' => $user->id,
            'level' => 'bronze',
            'total_points' => 0,
            'donation_count' => 0,
        ]);

        $oldLevel = $badge->level;
        $pointsEarned = self::POINTS_DONATION;

        // ৩তম donation-এ bonus
        $badge->donation_count++;
        if ($badge->donation_count % 3 === 0) {
            $pointsEarned += self::POINTS_STREAK_BONUS;
        }

        $this->addPoints($user, $pointsEarned, 'donation', "Donation #{$badge->donation_count}");

        // Badge reload + upgrade check
        $badge->refresh();
        $newLevel = $this->getLevel($badge->total_points);

        if ($newLevel !== $oldLevel) {
            $badge->update(['level' => $newLevel]);
            return [
                'points_earned' => $pointsEarned,
                'badge_upgraded' => true,
                'new_level' => $newLevel,
            ];
        }

        return [
            'points_earned' => $pointsEarned,
            'badge_upgraded' => false,
            'new_level' => null,
        ];
    }

    /**
     * Donation countdown timer calculate করো।
     * Medical rule: ৯০ দিন বিরতি লাগবে।
     *
     * @param  User  $user
     * @return array|null ['days_remaining' => int, 'eligible_date' => string, 'can_donate' => bool]
     */
    public function getDonationTimer(User $user): ?array
    {
        if (! $user->last_donation_date) {
            return null; // কোন donation history নেই
        }

        $eligibleDate = $user->last_donation_date->addDays(90);
        $today = Carbon::today();

        if ($today->greaterThanOrEqualTo($eligibleDate)) {
            return null; // Already eligible
        }

        $daysRemaining = $today->diffInDays($eligibleDate);

        return [
            'days_remaining' => $daysRemaining,
            'eligible_date' => $eligibleDate->format('F d, Y'),
            'can_donate' => false,
        ];
    }

    /**
     * পয়েন্ট transaction log-এ যোগ করো।
     * এবং DonorBadge-এর total_points আপডেট করো।
     *
     * @param  User    $user
     * @param  int     $points
     * @param  string  $reason      (registration|profile_complete|donation)
     * @param  string  $description (display এর জন্য)
     */
    protected function addPoints(User $user, int $points, string $reason, string $description): void
    {
        // Transaction log তৈরি করো
        DonorPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
            'description' => $description,
        ]);

        // Badge update করো
        $badge = $user->badge ?? DonorBadge::create([
            'user_id' => $user->id,
            'level' => 'bronze',
            'total_points' => 0,
            'donation_count' => 0,
        ]);

        $newTotal = $badge->total_points + $points;
        $newLevel = $this->getLevel($newTotal);

        $badge->update([
            'total_points' => $newTotal,
            'level' => $newLevel,
        ]);
    }

    /**
     * Total points থেকে badge level বের করো।
     *
     * @param  int  $totalPoints
     * @return string (bronze|silver|gold|hero)
     */
    protected function getLevel(int $totalPoints): string
    {
        if ($totalPoints >= self::THRESHOLD_HERO) {
            return 'hero';
        } elseif ($totalPoints >= self::THRESHOLD_GOLD) {
            return 'gold';
        } elseif ($totalPoints >= self::THRESHOLD_SILVER) {
            return 'silver';
        }
        return 'bronze';
    }
}
