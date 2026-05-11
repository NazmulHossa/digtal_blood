<?php

namespace App\Http\Controllers;

use App\Models\BloodConnection;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\District;
use App\Models\DonorBadge;
use App\Models\Upazila;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * DashboardController — Final Complete Version
 * ──────────────────────────────────────────────
 * Login করা ইউজারের ব্যক্তিগত dashboard।
 *
 * নতুন features (gamification + masking):
 *   - badge data (level, points, progress bar)
 *   - donation countdown timer
 *   - pending connection requests count (inbox badge)
 *   - phone_masked toggle
 *   - BadgeService দিয়ে auto points + badge upgrade
 */
class DashboardController extends Controller
{
    /**
     * Dashboard পেজ — GET /dashboard
     * ────────────────────────────────
     * BadgeService inject হয় Laravel-এর IoC Container থেকে।
     * Controller method-এ type-hint করলে Laravel auto-inject করে।
     */
    public function index(BadgeService $badgeService): Response
    {
        // User সব relationship সহ লোড করো
        $user = auth()->user()->load([
            'bloodGroup',
            'district',
            'upazila',
            'badge',       // DonorBadge (gamification)
        ]);

        // ── Badge Data ─────────────────────────────────────────
        // DonorBadge model থেকে display config বের করো।
        // যদি badge না থাকে (নতুন ইউজার), default bronze দেখাবে।
        $badge = $user->badge;
        if ($badge) {
            // Model-এ defined computed attributes যোগ করো
            // (JSON-এ পাঠাতে হবে, তাই manually set করা হচ্ছে)
            $badge->config              = DonorBadge::levels()[$badge->level];
            $badge->next_level_points   = $badge->next_level_points;
            $badge->progress_percentage = $badge->progress_percentage;
        } else {
            // প্রথমবার dashboard খুললে default bronze দেখাও
            $badge = (object) [
                'level'               => 'bronze',
                'total_points'        => 0,
                'donation_count'      => 0,
                'progress_percentage' => 0,
                'next_level_points'   => 100,
                'config'              => DonorBadge::levels()['bronze'],
            ];
        }

        // ── Donation Countdown Timer ────────────────────────────
        // BadgeService-এর getDonationTimer() থেকে data আনো।
        // এটি Vue-এ BadgeCard component-এ দেখাবে।
        $timer = $badgeService->getDonationTimer($user);

        // ── Connection Inbox Count ─────────────────────────────
        // Navbar-এ pending request count badge দেখানোর জন্য।
        $pendingConnectionsCount = BloodConnection::where('donor_id', $user->id)
                                                  ->where('status', 'pending')
                                                  ->count();

        // ── User-এর নিজের blood requests ──────────────────────
        $myRequests = BloodRequest::where('user_id', $user->id)
                                  ->with(['bloodGroup', 'district', 'upazila'])
                                  ->latest()
                                  ->take(5)
                                  ->get();

        // ── System-wide Stats ──────────────────────────────────
        $stats = [
            'total_donors'   => User::where('role', 'donor')->count(),
            'available_now'  => User::where('role', 'donor')
                                    ->where('is_available', true)
                                    ->count(),
            'open_requests'  => BloodRequest::open()->count(),
            'urgent_now'     => BloodRequest::open()->urgent()->count(),
        ];

        // ── Dropdown data (profile edit form) ─────────────────
        $bloodGroups = BloodGroup::orderBy('name')->get();
        $districts   = District::orderBy('name')->get();
        $upazilas    = $user->district_id
            ? Upazila::where('district_id', $user->district_id)->orderBy('name')->get()
            : collect();

        return Inertia::render('Dashboard', [
            'user'                    => $user,
            'badge'                   => $badge,
            'timer'                   => $timer,
            'pendingConnectionsCount' => $pendingConnectionsCount,
            'myRequests'              => $myRequests,
            'stats'                   => $stats,
            'bloodGroups'             => $bloodGroups,
            'districts'               => $districts,
            'upazilas'                => $upazilas,
        ]);
    }

    /**
     * Profile আপডেট — PATCH /dashboard/profile
     * ──────────────────────────────────────────
     * Donation date পরিবর্তন হলে BadgeService দিয়ে
     * পয়েন্ট দেওয়া হয় এবং badge upgrade হয়।
     */
    public function updateProfile(Request $request, BadgeService $badgeService)
    {
        $user = auth()->user();

        // পুরনো donation date মনে রাখো (comparison-এর জন্য)
        $oldDonationDate = $user->last_donation_date?->format('Y-m-d');

        $validated = $request->validate([
            'blood_group_id'     => 'nullable|exists:blood_groups,id',
            'district_id'        => 'nullable|exists:districts,id',
            'upazila_id'         => 'nullable|exists:upazilas,id',
            'phone'              => 'nullable|string|max:20',
            'phone_masked'       => 'boolean',
            'last_donation_date' => 'nullable|date|before_or_equal:today',
            'is_available'       => 'boolean',
        ]);

        $user->update($validated);

        // ── Gamification: নতুন donation date দেওয়া হয়েছে? ────
        $newDonationDate = $validated['last_donation_date'] ?? null;
        $dateChanged = $newDonationDate && $newDonationDate !== $oldDonationDate;

        if ($dateChanged) {
            // Donation award: +50 pts + streak bonus (প্রতি ৩তম donation-এ)
            $result = $badgeService->awardDonation($user);

            if ($result['badge_upgraded']) {
                // Badge level upgrade হয়েছে! Extra congratulation
                $levelLabel = DonorBadge::levels()[$result['new_level']]['label'];
                $message = "🎉 অভিনন্দন! আপনি {$levelLabel} badge পেয়েছেন! +{$result['points_earned']} পয়েন্ট।";
            } else {
                $message = "✅ রক্তদান রেকর্ড হয়েছে! +{$result['points_earned']} পয়েন্ট পেয়েছেন।";
            }
        } else {
            // Profile update হয়েছে — completion check করো
            $badgeService->awardProfileCompletion($user);
            $message = 'প্রোফাইল সফলভাবে আপডেট হয়েছে!';
        }

        return back()->with('success', $message);
    }
}