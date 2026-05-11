<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — মেইন Seeder
 * ─────────────────────────────
 * `php artisan db:seed` বা `php artisan migrate:fresh --seed`
 * চালালে এই file-টি প্রথমে run হয়।
 *
 * এটি অন্য seeders-কে call করে সঠিক ক্রমে।
 * ক্রম গুরুত্বপূর্ণ — কারণ BloodBankSeeder-এ foreign key আছে।
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // BloodBankSeeder-কে call করো
        // এই seeder-এ সব ডেমো ডেটা আছে
        $this->call([
            BloodBankSeeder::class,
        ]);
    }
}