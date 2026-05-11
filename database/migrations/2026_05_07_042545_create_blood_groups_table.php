<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_blood_groups_table
 * ──────────────────────────────────────
 * রক্তের ৮টি গ্রুপের টেবিল।
 * এটি প্রথমে তৈরি হবে কারণ users ও blood_requests এটির উপর নির্ভরশীল।
 *
 * ডেটা উদাহরণ:
 *   id=1, name="A+"
 *   id=2, name="A-"
 *   id=3, name="B+"
 *   ...
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 10)->unique()->comment('যেমন: A+, O-, AB+');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_groups');
    }
};