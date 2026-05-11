<?php

namespace App\Http\Controllers;

use App\Models\BloodConnection;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ============================================================
 * ConnectionController — Phone Masking System
 * ============================================================
 *
 * এই controller ডোনারের phone number সুরক্ষিত রাখে।
 *
 * কেন Phone Masking দরকার?
 * ─────────────────────────
 * সরাসরি phone দেখালে:
 *   ❌ Spam calls আসতে পারে
 *   ❌ ডোনার বিরক্ত হলে platform ছেড়ে দিতে পারে
 *   ❌ Privacy আইন লঙ্ঘন হতে পারে
 *
 * Connection system-এ:
 *   ✅ শুধু "Request to Connect" দেখায়
 *   ✅ Donor consent ছাড়া phone share হয় না
 *   ✅ Donor accepted হলে দুইজনই phone দেখে
 *   ✅ Donor সব request track করতে পারে
 *
 * Routes (web.php-এ যোগ করতে হবে):
 *   POST   /connections           → store()
 *   PATCH  /connections/{id}/respond → respond()
 *   GET    /connections           → inbox() (donor inbox)
 * ============================================================
 */
class ConnectionController extends Controller
{
    /**
     * "Request to Connect" পাঠাও — POST /connections
     * ───────────────────────────────────────────────
     * Recipient একজন donor-এর profile-এ এই button চাপে।
     * একটি BloodConnection record তৈরি হয় (status = pending)।
     *
     * @param  Request  $request
     *   - donor_id: int (required)
     *   - blood_request_id: int|null
     *   - message: string|null (কেন রক্ত দরকার)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_id'         => 'required|exists:users,id',
            'blood_request_id' => 'nullable|exists:blood_requests,id',
            'message'          => 'nullable|string|max:500',
        ]);

        $donorId = $validated['donor_id'];
        $me      = auth()->id();

        // নিজেকে নিজে request করা যাবে না
        if ($donorId === $me) {
            return back()->with('error', 'নিজেকে নিজে request করা যাবে না।');
        }

        // আগে pending request আছে কিনা check করো
        $exists = BloodConnection::where('requester_id', $me)
                                 ->where('donor_id', $donorId)
                                 ->where('status', 'pending')
                                 ->exists();

        if ($exists) {
            return back()->with('info', 'আপনি ইতিমধ্যে এই ডোনারকে request পাঠিয়েছেন। অনুগ্রহ করে অপেক্ষা করুন।');
        }

        // Connection request তৈরি করো
        BloodConnection::create([
            'requester_id'     => $me,
            'donor_id'         => $donorId,
            'blood_request_id' => $validated['blood_request_id'] ?? null,
            'message'          => $validated['message'] ?? null,
            'status'           => 'pending',
        ]);

        return back()->with('success', 'Connection request পাঠানো হয়েছে! ডোনার accept করলে আপনি phone পাবেন।');
    }

    /**
     * Donor একটি connection request-এ respond করে — PATCH /connections/{connection}/respond
     * ──────────────────────────────────────────────────────────────────────────────────────
     * Donor "Accept" বা "Decline" বাটন চাপলে এই method চলে।
     *
     * @param  BloodConnection  $connection  (Route Model Binding)
     * @param  Request          $request
     *   - action: 'accept' | 'decline'
     */
    public function respond(BloodConnection $connection, Request $request)
    {
        $request->validate([
            'action' => 'required|in:accept,decline',
        ]);

        // শুধু সেই donor respond করতে পারবে যার কাছে request গেছে
        if ($connection->donor_id !== auth()->id()) {
            abort(403, 'You are not authorized to respond to this request.');
        }

        // শুধু pending request respond করা যাবে
        if ($connection->status !== 'pending') {
            return back()->with('info', 'এই request-এ আগেই respond করা হয়েছে।');
        }

        $newStatus = $request->action === 'accept' ? 'accepted' : 'declined';

        $connection->update([
            'status'       => $newStatus,
            'responded_at' => now(),
        ]);

        $message = $newStatus === 'accepted'
            ? '✅ Request accept করা হয়েছে। এখন উভয়েই phone দেখতে পারবেন।'
            : 'Request decline করা হয়েছে।';

        return back()->with('success', $message);
    }

    /**
     * Donor-এর inbox — GET /connections
     * ───────────────────────────────────
     * সব pending connection requests দেখায়।
     * Dashboard-এর "Requests Received" section।
     *
     * @return Response
     */
    public function inbox(): Response
    {
        $pendingRequests = BloodConnection::where('donor_id', auth()->id())
            ->where('status', 'pending')
            ->with([
                'requester:id,name,blood_group_id',
                'requester.bloodGroup',
                'bloodRequest:id,blood_group_id,hospital_name',
            ])
            ->latest()
            ->get();

        $acceptedConnections = BloodConnection::where('donor_id', auth()->id())
            ->where('status', 'accepted')
            ->with(['requester:id,name,phone,blood_group_id'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Connections/Inbox', [
            'pendingRequests'      => $pendingRequests,
            'acceptedConnections'  => $acceptedConnections,
        ]);
    }

    /**
     * একজন requester-এর sent connections দেখাও।
     * "My Sent Requests" section।
     */
    public function sent(): Response
    {
        $sentConnections = BloodConnection::where('requester_id', auth()->id())
            ->with([
                'donor:id,name,blood_group_id,district_id,upazila_id',
                'donor.bloodGroup',
                'donor.upazila',
            ])
            ->latest()
            ->paginate(10);

        return Inertia::render('Connections/Sent', [
            'connections' => $sentConnections,
        ]);
    }
}