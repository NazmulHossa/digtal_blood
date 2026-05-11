<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model — Safe Complete Version
 * ─────────────────────────────────────
 * Breeze-এর default User model-এর উপর আমাদের
 * custom fields ও relationships যোগ করা হয়েছে।
 *
 * ★ IMPORTANT: $fillable-এ শুধু সেই fields আছে
 *   যেগুলোর migration লেখা হয়েছে।
 *   নতুন field যোগ করার আগে অবশ্যই migration চালাতে হবে।
 *
 * Custom fields (আমাদের migration-এ আছে):
 *   phone, phone_masked, blood_group_id,
 *   district_id, upazila_id, last_donation_date,
 *   is_available, role
 */
class User extends Authenticatable
{
    use Notifiable;

    // ── Mass Assignable Fields ────────────────────────────────
    protected $fillable = [
        // Breeze defaults
        'name',
        'email',
        'password',

        // আমাদের custom fields
        'phone',
        'phone_masked',
        'blood_group_id',
        'district_id',
        'upazila_id',
        'last_donation_date',
        'is_available',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'last_donation_date' => 'date',
        'is_available'       => 'boolean',
        'phone_masked'       => 'boolean',
        'password'           => 'hashed',
    ];

    // ══════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════

    /**
     * রক্তের গ্রুপ।
     * ব্যবহার: $user->bloodGroup->name  → "A+"
     */
    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    /**
     * জেলা।
     * ব্যবহার: $user->district->name  → "Chittagong"
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * উপজেলা।
     * ব্যবহার: $user->upazila->name  → "Sitakunda"
     */
    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class);
    }

    /**
     * এই user-এর blood requests।
     */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }

    /**
     * Gamification badge (one-to-one)।
     * ব্যবহার: $user->badge?->level  → "gold"
     *
     * ? (nullsafe) ব্যবহার করো — badge না থাকলে null হবে।
     */
    public function badge(): HasOne
    {
        return $this->hasOne(DonorBadge::class);
    }

    /**
     * Points transaction log।
     */
    public function points(): HasMany
    {
        return $this->hasMany(DonorPoint::class);
    }

    /**
     * Donor হিসেবে পাওয়া connection requests।
     * ব্যবহার: $user->receivedConnections()->pending()->get()
     */
    public function receivedConnections(): HasMany
    {
        return $this->hasMany(BloodConnection::class, 'donor_id');
    }

    /**
     * Requester হিসেবে পাঠানো connection requests।
     */
    public function sentConnections(): HasMany
    {
        return $this->hasMany(BloodConnection::class, 'requester_id');
    }

    // ══════════════════════════════════════════════════════════
    // QUERY SCOPES
    // ══════════════════════════════════════════════════════════

    /** শুধু donor রোলের ইউজার। */
    public function scopeDonors($query)
    {
        return $query->where('role', 'donor');
    }

    /** শুধু available ডোনার। */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    // ══════════════════════════════════════════════════════════
    // HELPER METHODS
    // ══════════════════════════════════════════════════════════

    /**
     * ডোনার এখন রক্ত দিতে পারবেন কিনা।
     * Medical rule: শেষ donation-এর পর ৯০ দিন বিরতি লাগবে।
     */
    public function canDonateNow(): bool
    {
        if (! $this->last_donation_date) {
            return true;
        }
        return $this->last_donation_date->diffInDays(Carbon::today()) >= 90;
    }

    /**
     * Accessor: পরবর্তী eligible donation date।
     * ব্যবহার: $user->next_eligible_date
     */
    public function getNextEligibleDateAttribute(): string
    {
        if (! $this->last_donation_date || $this->canDonateNow()) {
            return 'Eligible Now';
        }
        return $this->last_donation_date->addDays(90)->format('F d, Y');
    }

    /**
     * Phone publicly দেখানো যাবে কিনা।
     * phone_masked = true → ConnectButton দেখাবে।
     */
    public function showPhonePublicly(): bool
    {
        return ! ($this->phone_masked ?? false);
    }
}