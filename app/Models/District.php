<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * District Model
 * --------------
 * বাংলাদেশের জেলাগুলো স্টোর করে।
 * এই প্রজেক্টে মূলত চট্টগ্রাম বিভাগের জেলাগুলো focus করা হয়েছে।
 *
 * সম্পর্ক:
 *   District → hasMany → Upazila  (একটি জেলায় অনেক উপজেলা)
 *   District → hasMany → User     (একটি জেলায় অনেক ইউজার)
 *   District → hasMany → BloodRequest
 */
class District extends Model
{
    protected $fillable = ['name', 'bn_name'];

    /**
     * এই জেলার সকল উপজেলা।
     * ব্যবহার: District::find(1)->upazilas
     *          → [Sitakunda, Double Mooring, Mirsharai, ...]
     */
    public function upazilas(): HasMany
    {
        return $this->hasMany(Upazila::class);
    }

    /**
     * এই জেলার সকল নিবন্ধিত ইউজার/ডোনার।
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * এই জেলা থেকে পোস্ট করা সকল রক্তের রিকোয়েস্ট।
     */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }
}