<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_users_table
 * ─────────────────────────────
 * Laravel Breeze-এর default users table-টি modify করা হয়েছে।
 * নতুন columns যোগ করা হয়েছে:
 *   - phone           : মোবাইল নম্বর
 *   - blood_group_id  : রক্তের গ্রুপ (FK → blood_groups)
 *   - district_id     : জেলা (FK → districts)
 *   - upazila_id      : উপজেলা (FK → upazilas)
 *   - last_donation_date : শেষ রক্তদানের তারিখ
 *   - is_available    : এখন দিতে পারবেন কিনা
 *   - role            : ইউজারের ভূমিকা (admin/donor/recipient)
 *
 * ★ NOTE: এই migration টি blood_groups, districts, upazilas
 *   table তৈরির পরে চালাতে হবে (foreign key dependency)।
 *   তাই timestamp ওগুলোর চেয়ে বড় রাখো।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // ── Core Fields (Laravel Default) ──────────────────
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // ── Contact ────────────────────────────────────────
            $table->string('phone', 20)->nullable()->comment('বাংলাদেশি মোবাইল নম্বর');

            // ── Blood & Location (Foreign Keys) ────────────────
            // nullable() কারণ registration-এর সময় এগুলো না দিলেও চলে

            $table->foreignId('blood_group_id')
                  ->nullable()
                  ->constrained('blood_groups')  // blood_groups.id-কে reference করে
                  ->nullOnDelete();               // blood_group delete হলে null হয়ে যাবে

            $table->foreignId('district_id')
                  ->nullable()
                  ->constrained('districts')
                  ->nullOnDelete();

            $table->foreignId('upazila_id')
                  ->nullable()
                  ->constrained('upazilas')
                  ->nullOnDelete();

            // ── Donation Tracking ──────────────────────────────
            $table->date('last_donation_date')
                  ->nullable()
                  ->comment('শেষ রক্তদানের তারিখ — eligibility check-এ ব্যবহৃত');

            $table->boolean('is_available')
                  ->default(true)
                  ->comment('true = এখন রক্ত দিতে ইচ্ছুক');

            // ── Role ───────────────────────────────────────────
            // admin: সিস্টেম পরিচালনা করেন
            // donor: রক্ত দেন
            // recipient: রক্ত নেন / request করেন
            $table->enum('role', ['admin', 'donor', 'recipient'])
                  ->default('donor');

            $table->timestamps();
        });

        // ── Password Reset Tokens (Laravel Default) ────────────
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ── Sessions Table ─────────────────────────────────────
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};