<?php

namespace App\Services;

use App\Models\DonorBadge;
use App\Models\DonorPoint;
use App\Models\User;
use Carbon\Carbon;

/**
 * BadgeService — Gamification Engine
 * ====================================
 * ডোনারদের encourage করার জন্য badge system।
 */
class BadgeService
{
    const POINTS_REGISTRATION     = 10;
    const POINTS_PROFILE_COMPLETE = 20;
    const POINTS_DONATION         = 50;
    const POINTS_STREAK_BONUS     = 30;

    const THRESHOLD_SILVER = 100;
    const THRESHOLD_GOLD   = 300;
    const THRESHOLD_HERO   = 600;

    public function awardRegistration(User $user): void
    {
        DonorBadge::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 'bronze', 'total_points' => 0, 'donation_count' => 0]
        );

        $this->addPoints($user, self::POINTS_REGISTRATION, 'registration', 'Welcome bonus');
    }

    public function awardProfileCompletion(User $user): void
    {
        $isComplete = $user->blood_group_id
                   && $user->district_id
                   && $user->phone;

        if (! $isComplete) return;

        $alreadyAwarded = DonorPoint::where('user_id', $user->id)
                                    ->where('reason', 'profile_complete')
                                    ->exists();

        if ($alreadyAwarded) return;

        $this->addPoints($user, self::POINTS_PROFILE_COMPLETE, 'profile_complete', 'Profile fully completed');
    }

    /**
     * @return array ['points_earned' => int, 'badge_upgraded' => bool, 'new_level' => string, 'donation_count' => int]
     */
    public function awardDonation(User $user): array
    {
        $badge = DonorBadge::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 'bronze', 'total_points' => 0, 'donation_count' => 0]
        );

        $oldLevel     = $badge->level;
        $newCount     = $badge->donation_count + 1;
        $pointsEarned = self::POINTS_DONATION;

        if ($newCount % 3 === 0) {
            $pointsEarned += self::POINTS_STREAK_BONUS;
        }

        $this->addPoints($user, $pointsEarned, 'donation', "Donation #{$newCount}");

        $badge->increment('donation_count');
        $badge->refresh();

        $newLevel = $this->determineLevel($badge->total_points);
        $upgraded = $oldLevel !== $newLevel;

        if ($upgraded) {
            $badge->update(['level' => $newLevel]);
        }

        return [
            'points_earned'  => $pointsEarned,
            'badge_upgraded' => $upgraded,
            'new_level'      => $newLevel,
            'donation_count' => $badge->donation_count,
        ];
    }

    /**
     * @return array{eligible: bool, days_since: int, days_left: int, percentage: int, next_date: string|null, message: string}
     */
    public function getDonationTimer(User $user): array
    {
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
        $daysSince  = (int) $lastDate->diffInDays(Carbon::today());
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

    private function addPoints(User $user, int $points, string $reason, string $description = ''): void
    {
        DonorPoint::create([
            'user_id'     => $user->id,
            'points'      => $points,
            'reason'      => $reason,
            'description' => $description,
        ]);

        $badge = DonorBadge::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 'bronze', 'total_points' => 0, 'donation_count' => 0]
        );

        $badge->increment('total_points', $points);
        $badge->refresh();

        $newLevel = $this->determineLevel($badge->total_points);
        if ($newLevel !== $badge->level) {
            $badge->update(['level' => $newLevel]);
        }
    }

    private function determineLevel(int $totalPoints): string
    {
        if ($totalPoints >= self::THRESHOLD_HERO)   return 'hero';
        if ($totalPoints >= self::THRESHOLD_GOLD)   return 'gold';
        if ($totalPoints >= self::THRESHOLD_SILVER) return 'silver';
        return 'bronze';
    }
}