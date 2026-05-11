<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

/**
 * RegisteredUserController
 * ─────────────────────────
 * Laravel Breeze-এর default controller — customized।
 *
 * কী পরিবর্তন হয়েছে:
 *   1. 'role' field যোগ হয়েছে (donor / recipient)
 *   2. 'phone' field optional হিসেবে যোগ হয়েছে
 *   3. Registration-এ BadgeService::awardRegistration() call হয়
 *      (try-catch দিয়ে wrap — যাতে badge fail করলেও login ভাঙে না)
 */
class RegisteredUserController extends Controller
{
    /**
     * Registration form দেখাও — GET /register
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * নতুন user তৈরি করো — POST /register
     */
    public function store(Request $request): RedirectResponse
    {
        // ── Step 1: Validate input ─────────────────────────────
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255',
                           'unique:' . User::class],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['nullable', 'in:donor,recipient'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // ── Step 2: User তৈরি করো ─────────────────────────────
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone ?? null,
            'role'     => $request->role ?? 'donor',
        ]);

        // ── Step 3: Email verification event ──────────────────
        event(new Registered($user));

        // ── Step 4: Badge award (safe — will not break login) ──
        // try-catch দেওয়া হয়েছে কারণ:
        //   যদি donor_badges / donor_points table না থাকে,
        //   বা BadgeService-এ কোনো error হয়,
        //   তবুও registration সফল হবে।
        try {
            app(BadgeService::class)->awardRegistration($user);
        } catch (\Throwable $e) {
            // Badge award fail হলে শুধু log করো, registration cancel করো না
            \Illuminate\Support\Facades\Log::warning(
                'BadgeService::awardRegistration failed for user ' . $user->id,
                ['error' => $e->getMessage()]
            );
        }

        // ── Step 5: Login করো ─────────────────────────────────
        Auth::login($user);

        // Dashboard-এ redirect
        return redirect(route('dashboard', absolute: false));
    }
}