<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Upazila Model
 * -------------
 * বাংলাদেশের উপজেলা/থানা স্টোর করে।
 * প্রতিটি উপজেলা একটি নির্দিষ্ট জেলার অন্তর্গত।
 *
 * উদাহরণ: সীতাকুণ্ড → চট্টগ্রাম জেলা
 *
 * সম্পর্ক:
 *   Upazila → belongsTo → District  (প্রতিটি উপজেলা একটি জেলার)
 *   Upazila → hasMany   → User      (একটি উপজেলায় অনেক ইউজার)
 */
class Upazila extends Model
{
    protected $fillable = ['district_id', 'name', 'bn_name'];

    /**
     * এই উপজেলা যে জেলার অন্তর্গত।
     * ব্যবহার: $upazila->district->name  → "Chittagong"
     *
     * belongsTo মানে: Upazilas টেবিলে district_id foreign key আছে।
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * এই উপজেলার সকল নিবন্ধিত ইউজার/ডোনার।
     * ব্যবহার: $upazila->users  → সীতাকুণ্ডের সব ডোনার
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}