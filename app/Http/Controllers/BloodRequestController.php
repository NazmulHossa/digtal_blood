<?php

namespace App\Http\Controllers;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\District;
use App\Models\Upazila;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * BloodRequestController
 * ----------------------
 * রক্তের জরুরি রিকোয়েস্টের সকল কাজ এই controller-এ।
 *
 * Public:
 *   index()   → সব open রিকোয়েস্ট দেখানো (সবার জন্য)
 *
 * Authenticated:
 *   create()  → নতুন রিকোয়েস্ট তৈরির ফর্ম
 *   store()   → ফর্ম submit করলে database-এ save
 *   fulfill() → রিকোয়েস্ট fulfilled হিসেবে মার্ক করা
 */
class BloodRequestController extends Controller
{
    /**
     * সব open রিকোয়েস্টের তালিকা — GET /requests
     * ─────────────────────────────────────────────
     * এই পেজটি সবার জন্য public।
     * সবচেয়ে urgent ও নতুন রিকোয়েস্ট আগে দেখায়।
     */
    public function index(): Response
    {
        $requests = BloodRequest::open()
            ->with([
                'bloodGroup',           // রক্তের গ্রুপের নাম
                'district',             // জেলার নাম
                'upazila',              // উপজেলার নাম
                'user:id,name,phone',   // শুধু দরকারি columns (security)
            ])
            ->orderByDesc('is_urgent')  // Urgent আগে
            ->latest()                  // তারপর নতুনগুলো
            ->paginate(10);

        return Inertia::render('BloodRequests/Index', [
            'requests' => $requests,
        ]);
    }

    /**
     * নতুন রিকোয়েস্টের ফর্ম — GET /requests/create
     * ─────────────────────────────────────────────────
     * Authenticated users only (routes/web.php-এ auth middleware আছে)।
     */
    public function create(): Response
    {
        return Inertia::render('BloodRequests/Create', [
            'bloodGroups' => BloodGroup::orderBy('name')->get(),
            'districts'   => District::orderBy('name')->get(),
        ]);
    }

    /**
     * নতুন রিকোয়েস্ট save করো — POST /requests
     * ─────────────────────────────────────────────
     * Laravel Validation → Database Save → Redirect
     */
    public function store(Request $request)
    {
        // ── Validation ────────────────────────────────────────────
        // Laravel স্বয়ংক্রিয়ভাবে validation error থাকলে
        // Inertia দিয়ে form.errors-এ পাঠায়।
        $validated = $request->validate([
            'blood_group_id' => 'required|exists:blood_groups,id',
            'district_id'    => 'required|exists:districts,id',
            'upazila_id'     => 'nullable|exists:upazilas,id',
            'hospital_name'  => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:500',
            'needed_by'      => 'nullable|date|after:now',
            'units_needed'   => 'required|integer|min:1|max:10',
            'notes'          => 'nullable|string|max:1000',
            'contact_phone'  => 'nullable|string|max:20',
            'is_urgent'      => 'boolean',
        ]);

        // Authenticated user-এর ID যোগ করো
        $validated['user_id'] = auth()->id();

        // Database-এ save করো
        BloodRequest::create($validated);

        // Redirect করো success message সহ
        return redirect()
            ->route('requests.index')
            ->with('success', 'আপনার রক্তের রিকোয়েস্ট সফলভাবে পোস্ট হয়েছে!');
    }

    /**
     * রিকোয়েস্ট fulfilled মার্ক করো — PATCH /requests/{bloodRequest}/fulfill
     * ──────────────────────────────────────────────────────────────────────────
     * শুধু request-এর owner করতে পারবে।
     */
    public function fulfill(BloodRequest $bloodRequest)
    {
        // শুধু নিজের request fulfill করা যাবে
        if ($bloodRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $bloodRequest->update(['status' => 'fulfilled']);

        return back()->with('success', 'রিকোয়েস্ট fulfilled হিসেবে মার্ক করা হয়েছে। ধন্যবাদ!');
    }
}