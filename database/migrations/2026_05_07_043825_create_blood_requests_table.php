<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_blood_requests_table
 * ────────────────────────────────────────
 * রক্তের জরুরি রিকোয়েস্ট পোস্টের টেবিল।
 * এটি সবার শেষে তৈরি হবে কারণ:
 *   - users table-এর উপর নির্ভরশীল (user_id FK)
 *   - blood_groups table-এর উপর নির্ভরশীল (blood_group_id FK)
 *   - districts table-এর উপর নির্ভরশীল (district_id FK)
 *   - upazilas table-এর উপর নির্ভরশীল (upazila_id FK)
 *
 * Status Flow:
 *   'open' → 'fulfilled' (রক্ত পাওয়া গেছে)
 *          → 'closed'    (ম্যানুয়ালি বন্ধ)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();

            // ── Relationships ───────────────────────────────────
            // যে ইউজার request করেছেন
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');  // ইউজার delete হলে তার request-ও যাবে

            // কোন রক্ত দরকার
            $table->foreignId('blood_group_id')
                  ->constrained('blood_groups');

            // কোথায় দরকার (জেলা)
            $table->foreignId('district_id')
                  ->constrained('districts');

            // কোথায় দরকার (উপজেলা) — optional
            $table->foreignId('upazila_id')
                  ->nullable()
                  ->constrained('upazilas')
                  ->nullOnDelete();

            // ── Location Details ────────────────────────────────
            $table->string('hospital_name')->nullable()->comment('হাসপাতালের নাম');
            $table->text('address')->nullable()->comment('বিস্তারিত ঠিকানা');

            // ── Requirements ────────────────────────────────────
            $table->dateTime('needed_by')->nullable()->comment('কখনের মধ্যে দরকার');

            $table->unsignedTinyInteger('units_needed')
                  ->default(1)
                  ->comment('কত ব্যাগ রক্ত দরকার');

            $table->text('notes')->nullable()->comment('অতিরিক্ত তথ্য');

            // ── Contact ─────────────────────────────────────────
            $table->string('contact_phone', 20)->nullable()->comment('যোগাযোগের নম্বর');

            // ── Status ──────────────────────────────────────────
            $table->enum('status', ['open', 'fulfilled', 'closed'])
                  ->default('open')
                  ->comment('open=সক্রিয়, fulfilled=ব্যবস্থা হয়েছে, closed=বন্ধ');

            $table->boolean('is_urgent')
                  ->default(false)
                  ->comment('true হলে লাল URGENT badge দেখাবে');

            $table->timestamps();

            // ── Indexes for Search Performance ──────────────────
            // এই columns দিয়ে বেশি search হয়, তাই index দিলে দ্রুত হবে
            $table->index('status');
            $table->index('is_urgent');
            $table->index(['district_id', 'status']); // Composite index
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};