<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_upazilas_table
 * ──────────────────────────────────
 * উপজেলা/থানার টেবিল।
 * প্রতিটি উপজেলা একটি নির্দিষ্ট জেলার অন্তর্গত।
 *
 * Foreign Key: district_id → districts.id
 *   মানে: কোনো upazila তার parent district ছাড়া থাকতে পারবে না।
 *   onDelete('cascade'): জেলা delete হলে সেই জেলার সব উপজেলাও delete হবে।
 *
 * Unique Constraint: (district_id, name)
 *   মানে: একই জেলায় একই নামের দুটি উপজেলা থাকতে পারবে না।
 *   কিন্তু ভিন্ন জেলায় একই নাম থাকতে পারে।
 *
 * ডেটা উদাহরণ:
 *   id=1, district_id=1, name="Sitakunda",    bn_name="সীতাকুণ্ড"
 *   id=2, district_id=1, name="Double Mooring", bn_name="ডবলমুরিং"
 *   id=3, district_id=1, name="Mirsharai",    bn_name="মীরসরাই"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upazilas', function (Blueprint $table) {
            $table->id();

            // Foreign Key: প্রতিটি উপজেলা একটি জেলার
            $table->foreignId('district_id')
                  ->constrained('districts')
                  ->onDelete('cascade');

            $table->string('name', 100)->comment('ইংরেজিতে উপজেলার নাম');
            $table->string('bn_name', 100)->nullable()->comment('বাংলায় উপজেলার নাম');
            $table->timestamps();

            // একই জেলায় একই নামের উপজেলা থাকবে না
            $table->unique(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upazilas');
    }
};