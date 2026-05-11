<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_districts_table
 * ───────────────────────────────────
 * বাংলাদেশের জেলাগুলো স্টোর করার টেবিল।
 * এই প্রজেক্টে মূলত চট্টগ্রাম বিভাগের জেলাগুলো ব্যবহার করা হবে।
 *
 * bn_name: বাংলায় জেলার নাম (display-এর জন্য optional)
 *
 * ডেটা উদাহরণ:
 *   id=1, name="Chittagong", bn_name="চট্টগ্রাম"
 *   id=2, name="Cox's Bazar", bn_name="কক্সবাজার"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('ইংরেজিতে জেলার নাম');
            $table->string('bn_name', 100)->nullable()->comment('বাংলায় জেলার নাম');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};