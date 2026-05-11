<?php

namespace App\Services;

use App\Models\DonorBadge;
use App\Models\DonorPoint;
use App\Models\User;

/**
 * ============================================================
 * BadgeService — Gamification Business Logic
 * ============================================================
 *
 * এই Service class-টি সব gamification logic ধরে রাখে।
 * Controller বা Model-এ না রেখে Service-এ রাখার কারণ:
 *
 *   Single Responsibility: Controller শুধু HTTP handle করে,
 *   Service business logic handle করে।
 *
 *   Reusability: যেকোনো controller থেকে call করা যায়।
 *   যেমন: DonorController বা DashboardController দুটো থেকেই
 *   BadgeService::awardDonation($user) call করতে পারব।
 *
 * USAGE:
 *   // ডোনেশনের পর badge আপডেট করো
 *   app(BadgeService::class)->awardDonation($user);
 *
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
     * ডোনেশন রেকর্ড করা হলে পয়েন্ট + donation count আপডেট করো।
     * Streak bonus-ও check করো।
     *
     * @param  User  $user
     * @return array  ['points_earned' => int, 'badge_upgraded' => bool, 'new_level' => string]
     */
    public function awardDonation(User $user): array
    {
        $badge = DonorBadge::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 'bronze', 'total_points' => 0, 'donation_count' => 0]
        );

        $oldLevel = $badge->level;
        $pointsEarned = self::POINTS_DONATION;

        // ── Base donation points দাও ────────────────────────────
        $this->addPoints($user, self::POINTS_DONATION, 'donation', 'Blood donation recorded');

        // ── Donation count বাড়াও ───────────────────────────────
        $badge->increment('donation_count');
        $badge->refresh();

        // ── Streak Bonus: প্রতি ৩য় ডোনেশনে extra points ───────
        // 3rd, 6th, 9th donation-এ bonus দেওয়া হয়।
        if ($badge->donation_count % 3 === 0) {
            $this->addPoints(
                $user,
                self::POINTS_STREAK_BONUS,
                'streak_bonus',
                "Streak bonus — {$badge->donation_count}th donation!"
            );
            $pointsEarned += self::POINTS_STREAK_BONUS;
        }

        // Badge refresh করো
        $badge->refresh();

        return [
            'points_earned'   => $pointsEarned,
            'badge_upgraded'  => $badge->level !== $oldLevel,
            'new_level'       => $badge->level,
            'donation_count'  => $badge->donation_count,
        ];
    }

    /**
     * পয়েন্ট যোগ করো এবং badge level আপডেট করো।
     * এটি private — সরাসরি call করা যাবে না।
     *
     * @param  User    $user
     * @param  int     $points    কত পয়েন্ট দেওয়া হবে
     * @param  string  $reason    কেন দেওয়া হচ্ছে
     * @param  string  $note      Optional বিস্তারিত
     */
    private function addPoints(User $user, int $points, string $reason, string $note = ''): void
    {
        // ১. Point transaction log করো
        DonorPoint::create([
            'user_id' => $user->id,
            'points'  => $points,
            'reason'  => $reason,
            'note'    => $note,
        ]);

        // ২. Badge-এর total_points বাড়াও
        $badge = DonorBadge::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 'bronze', 'total_points' => 0, 'donation_count' => 0]
        );

        $badge->increment('total_points', $points);
        $badge->refresh();

        // ৩. নতুন total_points দিয়ে badge level আপডেট করো
        $newLevel = $this->calculateLevel($badge->total_points);
        if ($newLevel !== $badge->level) {
            $badge->update(['level' => $newLevel]);
        }
    }

    /**
     * মোট পয়েন্ট থেকে badge level বের করো।
     *
     * @param  int  $totalPoints
     * @return string  'bronze'|'silver'|'gold'|'hero'
     */
    private function calculateLevel(int $totalPoints): string
    {
        if ($totalPoints >= self::THRESHOLD_HERO)   return 'hero';
        if ($totalPoints >= self::THRESHOLD_GOLD)   return 'gold';
        if ($totalPoints >= self::THRESHOLD_SILVER) return 'silver';
        return 'bronze';
    }

    /**
     * Donation Countdown Timer-এর জন্য data বের করো।
     * User-এর last_donation_date থেকে হিসাব করে।
     *
     * @param  User  $user
     * @return array{
     *   eligible: bool,
     *   days_since: int,
     *   days_left: int,
     *   percentage: int,      0–100 (progress towards 90 days)
     *   next_date: string,
     *   message: string,
     * }
     */
    public function getDonationTimer(User $user): array
    {
        // কখনো donate না করলে এখনই eligible
        if (! $user->last_donation_date) {
            return [
                'eligible'   => true,
                'days_since' => 0,
                'days_left'  => 0,
                'percentage' => 100,
                'next_date'  => null,
                'message'    => 'আপনি এখনই রক্ত দিতে পারবেন!',
            ];
        }

        $lastDate   = $user->last_donation_date;
        $daysSince  = $lastDate->diffInDays(now());
        $daysLeft   = max(0, 90 - $daysSince);
        $nextDate   = $lastDate->copy()->addDays(90);
        $percentage = min(100, (int) round(($daysSince / 90) * 100));

        if ($daysSince >= 90) {
            return [
                'eligible'   => true,
                'days_since' => $daysSince,
                'days_left'  => 0,
                'percentage' => 100,
                'next_date'  => $nextDate->format('Y-m-d'),
                'message'    => "✅ আপনি এখন আবার রক্ত দিতে পারবেন! শেষ donation {$daysSince} দিন আগে।",
            ];
        }

        return [
            'eligible'   => false,
            'days_since' => $daysSince,
            'days_left'  => $daysLeft,
            'percentage' => $percentage,
            'next_date'  => $nextDate->format('Y-m-d'),
            'message'    => "আরও {$daysLeft} দিন পরে আবার donate করতে পারবেন। ({$nextDate->format('d M Y')})",
        ];
    }
}