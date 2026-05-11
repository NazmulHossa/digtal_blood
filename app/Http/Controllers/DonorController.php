<?php

namespace App\Http\Controllers;

use App\Models\BloodConnection;
use App\Models\BloodGroup;
use App\Models\District;
use App\Models\DonorBadge;
use App\Models\Upazila;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * DonorController — Final Complete Version
 * ──────────────────────────────────────────
 * ★ CORE FEATURE: Eloquent when() দিয়ে Dynamic Search Filter
 *
 * when($value, $callback):
 *   $value truthy → WHERE clause যোগ হয়
 *   $value falsy  → skip, কিছু হয় না
 *
 * এতে তিনটি optional filter clean ভাবে chain করা যায়।
 */
class DonorController extends Controller
{
    /**
     * ডোনার সার্চ পেজ — GET /donors
     * URL params: ?blood_group_id=1&district_id=1&upazila_id=3
     */
    public function index(Request $request): Response
    {
        // with() = Eager Loading → N+1 সমস্যা এড়ানো হচ্ছে
        $query = User::query()
            ->where('role', 'donor')
            ->where('is_available', true)
            ->with(['bloodGroup', 'district', 'upazila', 'badge']);

        // ★ Eloquent when() — optional filters
        $query->when(
            $request->blood_group_id,
            fn ($q, $v) => $q->where('blood_group_id', $v)
        );
        $query->when(
            $request->district_id,
            fn ($q, $v) => $q->where('district_id', $v)
        );
        $query->when(
            $request->upazila_id,
            fn ($q, $v) => $q->where('upazila_id', $v)
        );

        $donors = $query->latest()->paginate(12)->withQueryString();

        // Badge display config যোগ করো
        $donors->getCollection()->transform(function ($donor) {
            if ($donor->badge) {
                $donor->badge->config = DonorBadge::levels()[$donor->badge->level];
            }
            return $donor;
        });

        return Inertia::render('Donors/Index', [
            'donors'      => $donors,
            'bloodGroups' => BloodGroup::orderBy('name')->get(),
            'districts'   => District::orderBy('name')->get(),
            'upazilas'    => $request->district_id
                ? Upazila::where('district_id', $request->district_id)->orderBy('name')->get()
                : collect(),
            'filters'     => $request->only(['blood_group_id', 'district_id', 'upazila_id']),
        ]);
    }

    /**
     * একজন ডোনারের প্রোফাইল — GET /donors/{user}
     * Phone Masking + Connection Status + Badge সহ
     */
    public function show(User $user): Response
    {
        abort_unless($user->role === 'donor', 404);

        $user->load(['bloodGroup', 'district', 'upazila', 'badge']);

        if ($user->badge) {
            $user->badge->config              = DonorBadge::levels()[$user->badge->level];
            $user->badge->next_level_points   = $user->badge->next_level_points;
            $user->badge->progress_percentage = $user->badge->progress_percentage;
        }

        // Connection status বের করো (phone masking-এর জন্য)
        $connectionStatus = 'none';
        $donorPhone       = null;
        $isOwnProfile     = false;

        if (auth()->check()) {
            $authId = auth()->id();

            if ($authId === $user->id) {
                $isOwnProfile = true;
                $donorPhone   = $user->phone;
            } else {
                $connection = BloodConnection::where('requester_id', $authId)
                                             ->where('donor_id', $user->id)
                                             ->latest()->first();
                if ($connection) {
                    $connectionStatus = $connection->status;
                    if ($connection->status === 'accepted') {
                        $donorPhone = $user->phone;
                    }
                }
            }
        }

        return Inertia::render('Donors/Show', [
            'donor'            => $user,
            'connectionStatus' => $connectionStatus,
            'donorPhone'       => $donorPhone,
            'isOwnProfile'     => $isOwnProfile,
        ]);
    }

    /**
     * Upazila list — GET /api/upazilas?district_id=1
     * Vue cascading dropdown-এর জন্য JSON response
     */
    public function getUpazilas(Request $request)
    {
        $request->validate(['district_id' => 'required|exists:districts,id']);

        return response()->json(
            Upazila::where('district_id', $request->district_id)
                   ->orderBy('name')
                   ->get(['id', 'name', 'bn_name'])
        );
    }
}