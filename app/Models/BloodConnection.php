<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BloodConnection Model
 * ──────────────────────
 * Phone Masking system-এর মূল model।
 *
 * Flow:
 *   1. Recipient → donor profile-এ "Request to Connect" চাপে
 *   2. এই table-এ record তৈরি: status = 'pending'
 *   3. Donor → inbox-এ দেখে → Accept বা Decline করে
 *   4. Accept → status = 'accepted' → দুইজনই phone দেখতে পারে
 *   5. Decline → status = 'declined' → phone share হয় না
 *
 * Privacy benefit:
 *   ডোনার যদি না চান → decline করলেই হয়
 *   Spam call বা unwanted contact বন্ধ করা যায়
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

    // ── Relationships ─────────────────────────────────────────

    /**
     * Request পাঠিয়েছে যে (recipient/requester)।
     * ব্যবহার: $conn->requester->name → "Patient Name"
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Request পেয়েছে যে (donor)।
     * ব্যবহার: $conn->donor->name → "Rahim Uddin"
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    /**
     * সম্পর্কিত blood request (যদি থাকে)।
     * Recipient কোন specific request-এর জন্য connect করতে চাইছে।
     */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    // ── Query Scopes ──────────────────────────────────────────

    /**
     * শুধু pending connections।
     * ব্যবহার: BloodConnection::pending()->where('donor_id', $id)->get()
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * শুধু accepted connections।
     * ব্যবহার: BloodConnection::accepted()->get()
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}