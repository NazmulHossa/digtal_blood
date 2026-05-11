<?php

use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\ConnectionController; // নতুন Controller ইম্পোর্ট করো
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ══════════════════════════════════════════════════════════════
// PUBLIC ROUTES — সবার জন্য উন্মুক্ত
// ══════════════════════════════════════════════════════════════

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'urgentRequests' => \App\Models\BloodRequest::open()
                            ->urgent()
                            ->with(['bloodGroup', 'district', 'upazila'])
                            ->latest()
                            ->take(6)
                            ->get(),
        'stats' => [
            'total_donors'  => \App\Models\User::where('role', 'donor')->count(),
            'open_requests' => \App\Models\BloodRequest::open()->count(),
            'districts'     => \App\Models\District::count(),
        ],
    ]);
})->name('home');

Route::get('/donors', [DonorController::class, 'index'])->name('donors.index');
Route::get('/donors/{user}', [DonorController::class, 'show'])->name('donors.show');
Route::get('/requests', [BloodRequestController::class, 'index'])->name('requests.index');


// ══════════════════════════════════════════════════════════════
// AJAX API ROUTES — Vue-এর axios calls-এর জন্য
// ══════════════════════════════════════════════════════════════

Route::get('/api/upazilas', [DonorController::class, 'getUpazilas'])->name('api.upazilas');


// ══════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES — Login করা লাগবে
// ══════════════════════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {

    /** DASHBOARD — এখানে এখন Badge এবং Timer ডেটা লোড হবে */
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    /** UPDATE PROFILE — এখানে এখন Donation Date আপডেট হলে Badge Points বাড়বে */
    Route::patch('/dashboard/profile', [DashboardController::class, 'updateProfile'])
         ->name('dashboard.updateProfile');

    /** BLOOD REQUEST MANAGEMENT */
    Route::get('/requests/create', [BloodRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [BloodRequestController::class, 'store'])->name('requests.store');
    Route::patch('/requests/{bloodRequest}/fulfill', [BloodRequestController::class, 'fulfill'])->name('requests.fulfill');

    // ══════════════════════════════════════════════════════════
    // 🛡️ PHONE MASKING & CONNECTIONS — নতুন ফিচার
    // ══════════════════════════════════════════════════════════

    /** রিসিভড ইনবক্স — ডোনার দেখতে পাবেন কারা তার সাথে কানেক্ট হতে চায় */
    Route::get('/connections/inbox', [ConnectionController::class, 'inbox'])
         ->name('connections.inbox');

    /** কানেকশন রিকোয়েস্ট পাঠানো — 'Connect' বাটনে ক্লিক করলে এটি কাজ করবে */
    Route::post('/connections', [ConnectionController::class, 'store'])
         ->name('connections.store');

    /** রিকোয়েস্ট রেসপন্স — ডোনার রিকোয়েস্ট Accept বা Decline করবেন */
    Route::patch('/connections/{connection}/respond', [ConnectionController::class, 'respond'])
         ->name('connections.respond');

    /** সেন্ট রিকোয়েস্ট — ইউজার দেখতে পাবেন তিনি কাদের রিকোয়েস্ট পাঠিয়েছেন */
    Route::get('/connections/sent', [ConnectionController::class, 'sent'])
         ->name('connections.sent');
});

// Laravel Breeze-এর auth routes
require __DIR__ . '/auth.php';