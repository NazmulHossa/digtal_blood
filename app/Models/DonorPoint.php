<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DonorPoint Model
 * ────────────────
 * প্রতিটি পয়েন্ট অর্জনের transaction log রাখে।
 *
 * ব্যবহার:
 *   - User registration → +10 pts
 *   - Profile complete → +20 pts
 *   - Each donation → +50 pts
 *   - Every 3rd donation → +30 bonus pts
 */
class DonorPoint extends Model
{
    protected $table = 'donor_points';
    
    protected $fillable = [
        'user_id',
        'points',
        'reason',      // registration, profile_complete, donation
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationship ──────────────────────────────────────────

    /**
     * এই পয়েন্ট কার।
     * ব্যবহার: $point->user->name → "Rahim Uddin"
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
