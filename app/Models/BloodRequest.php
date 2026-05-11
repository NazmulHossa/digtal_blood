<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BloodRequest Model
 * ------------------
 * রক্তের জরুরি রিকোয়েস্ট পোস্ট স্টোর করে।
 * একজন recipient যখন রক্তের প্রয়োজন হয়, তখন এই মডেলের
 * মাধ্যমে একটি public post তৈরি হয়।
 *
 * Status lifecycle:
 *   open → fulfilled (রক্তের ব্যবস্থা হয়েছে)
 *        → closed    (ম্যানুয়ালি বন্ধ করা হয়েছে)
 */
class BloodRequest extends Model
{
    protected $fillable = [
        'user_id',
        'blood_group_id',
        'district_id',
        'upazila_id',
        'hospital_name',
        'address',
        'needed_by',
        'units_needed',
        'notes',
        'contact_phone',
        'status',
        'is_urgent',
    ];

    protected $casts = [
        'needed_by' => 'datetime',
        'is_urgent' => 'boolean',
    ];

    // ══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════════════

    /**
     * যে ইউজার এই রিকোয়েস্ট পোস্ট করেছেন।
     * ব্যবহার: $request->user->name  → "Rahim Uddin"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * কোন রক্তের গ্রুপ দরকার।
     * ব্যবহার: $request->bloodGroup->name  → "O-"
     */
    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    /**
     * কোন জেলায় রক্ত দরকার।
     * ব্যবহার: $request->district->name  → "Chittagong"
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * কোন উপজেলায় রক্ত দরকার।
     * ব্যবহার: $request->upazila->name  → "Sitakunda"
     */
    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class);
    }

    // ══════════════════════════════════════════════════════════════
    // QUERY SCOPES
    // ══════════════════════════════════════════════════════════════

    /**
     * শুধুমাত্র open (active) রিকোয়েস্ট আনতে।
     * ব্যবহার: BloodRequest::open()->get()
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * শুধুমাত্র জরুরি (urgent) রিকোয়েস্ট আনতে।
     * ব্যবহার: BloodRequest::open()->urgent()->get()
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }
}