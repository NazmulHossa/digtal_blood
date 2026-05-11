<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Gamification + Phone Masking Tables
 * ============================================================
 *
 * তিনটি নতুন টেবিল যোগ হচ্ছে:
 *
 * ১. donor_points
 *    প্রতিটি ডোনেশনের জন্য পয়েন্ট track করে।
 *    পয়েন্ট জমলে badge upgrade হয়।
 *
 * ২. donor_badges
 *    প্রতিটি ডোনারের অর্জিত badge ও level স্টোর করে।
 *    Level: bronze → silver → gold → hero
 *
 * ৩. blood_connections
 *    Phone Masking-এর জন্য।
 *    Recipient "Request to Connect" পাঠালে এখানে record হয়।
 *    Donor "Accept" করলে তবেই phone নম্বর visible হয়।
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── TABLE 1: donor_points ────────────────────────────────
        // প্রতিটি ডোনেশন action-এর জন্য পয়েন্ট লগ রাখে।
        // এটি audit trail হিসেবেও কাজ করে।
        Schema::create('donor_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // কত পয়েন্ট পেয়েছে এই transaction-এ
            $table->unsignedSmallInteger('points');

            // কেন পয়েন্ট দেওয়া হয়েছে
            // 'donation'     = রক্ত দিয়েছে (+50 pts)
            // 'registration' = প্রথমবার register (+10 pts)
            // 'profile_complete' = profile পূরণ (+20 pts)
            // 'streak'       = ৩ বার consecutive donation (+30 pts)
            $table->enum('reason', [
                'donation',
                'registration',
                'profile_complete',
                'streak_bonus',
            ]);

            $table->text('note')->nullable(); // Optional admin note
            $table->timestamps();

            $table->index('user_id'); // Fast lookup by user
        });

        // ── TABLE 2: donor_badges ───────────────────────────────
        // প্রতিটি ডোনারের বর্তমান badge/level।
        // একজন ডোনারের একটিই row থাকবে এখানে।
        Schema::create('donor_badges', function (Blueprint $table) {
            $table->id();

            // One-to-one: একজন ডোনার → একটি badge record
            $table->foreignId('user_id')
                  ->unique()              // unique = one badge row per user
                  ->constrained('users')
                  ->onDelete('cascade');

            // Badge level (পয়েন্ট অনুযায়ী auto-upgrade হবে)
            // bronze: 0–99 pts  | silver: 100–299 pts
            // gold:   300–599 pts | hero: 600+ pts
            $table->enum('level', ['bronze', 'silver', 'gold', 'hero'])
                  ->default('bronze');

            // মোট জমানো পয়েন্ট (denormalized for fast read)
            $table->unsignedInteger('total_points')->default(0);

            // মোট কতবার রক্ত দিয়েছে
            $table->unsignedTinyInteger('donation_count')->default(0);

            $table->timestamps();
        });

        // ── TABLE 3: blood_connections ──────────────────────────
        // Phone Masking system।
        //
        // Flow:
        //   Recipient → clicks "Request to Connect" on donor profile
        //   → একটি record insert হয় (status = 'pending')
        //   → Donor তার dashboard-এ notification দেখে
        //   → Donor "Accept" করলে → status = 'accepted'
        //   → এখন দুইজনই একে অপরের phone দেখতে পারবে
        //   → Donor "Decline" করলে → status = 'declined'
        Schema::create('blood_connections', function (Blueprint $table) {
            $table->id();

            // কে connect করতে চাইছে (recipient)
            $table->foreignId('requester_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // কার সাথে connect হতে চাইছে (donor)
            $table->foreignId('donor_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // কোন blood request-এর জন্য (optional)
            $table->foreignId('blood_request_id')
                  ->nullable()
                  ->constrained('blood_requests')
                  ->nullOnDelete();

            // Connection-এর অবস্থা
            $table->enum('status', ['pending', 'accepted', 'declined'])
                  ->default('pending');

            // Recipient-এর পাঠানো message
            $table->text('message')->nullable();

            // Donor কখন respond করেছে
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            // একজন requester একই donor-এ একটিই pending request করতে পারবে
            $table->unique(['requester_id', 'donor_id', 'status']);

            $table->index(['donor_id', 'status']); // Donor inbox lookup
        });

        // ── users টেবিলে নতুন columns যোগ ──────────────────────
        // phone_masked = true হলে সবার কাছে phone hide থাকবে
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('phone_masked')
                  ->default(false)
                  ->after('phone')
                  ->comment('true = phone number publicly hidden, connect request required');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_masked');
        });
        Schema::dropIfExists('blood_connections');
        Schema::dropIfExists('donor_badges');
        Schema::dropIfExists('donor_points');
    }
};
