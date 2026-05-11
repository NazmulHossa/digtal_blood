<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BloodGroup Model
 * ----------------
 * রক্তের ৮টি গ্রুপ স্টোর করে: A+, A-, B+, B-, AB+, AB-, O+, O-
 *
 * একটি BloodGroup-এর অনেক User ও অনেক BloodRequest থাকতে পারে।
 * সম্পর্ক: hasMany (one-to-many)
 */
class BloodGroup extends Model
{
    protected $fillable = ['name'];

    /**
     * এই রক্তের গ্রুপের সকল নিবন্ধিত ডোনার।
     * ব্যবহার: BloodGroup::find(1)->users  → সব A+ ডোনার
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * এই রক্তের গ্রুপের জন্য সকল রিকোয়েস্ট।
     * ব্যবহার: BloodGroup::find(3)->bloodRequests  → সব B+ রিকোয়েস্ট
     */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }
}