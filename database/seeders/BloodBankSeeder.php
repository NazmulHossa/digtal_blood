<?php

namespace Database\Seeders;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\District;
use App\Models\Upazila;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * BloodBankSeeder
 * ───────────────
 * সম্পূর্ণ ডেমো ডেটা এই seeder-এ আছে:
 *   ১. রক্তের গ্রুপ (৮টি)
 *   ২. জেলা (৪টি — চট্টগ্রাম focus)
 *   ৩. উপজেলা (চট্টগ্রামের সব ২৩টি + অন্যান্য)
 *   ৪. Admin ও sample donor accounts
 *   ৫. Sample blood requests
 *
 * চালানোর উপায়:
 *   php artisan db:seed
 *   অথবা: php artisan migrate:fresh --seed
 */
class BloodBankSeeder extends Seeder
{
    public function run(): void
    {
        // ক্রম অনুযায়ী seed করো
        $this->seedBloodGroups();
        $this->seedDistrictsAndUpazilas();
        $this->seedUsers();
        $this->seedBloodRequests();
      
        // যদি gamification models থাকে
    }

    // ══════════════════════════════════════════════════════════════
    // ১. রক্তের গ্রুপ Seed
    // ══════════════════════════════════════════════════════════════
    private function seedBloodGroups(): void
    {
        // ABO ও Rh factor মিলিয়ে ৮টি গ্রুপ
        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        foreach ($groups as $name) {
            // firstOrCreate: আগে থেকে থাকলে নতুন করে insert করবে না
            BloodGroup::firstOrCreate(['name' => $name]);
        }

        $this->command->info('✅ রক্তের গ্রুপ (৮টি) seed হয়েছে।');
    }

    // ══════════════════════════════════════════════════════════════
    // ২. জেলা ও উপজেলা Seed
    // ══════════════════════════════════════════════════════════════
    private function seedDistrictsAndUpazilas(): void
    {
        $data = [
            // ── চট্টগ্রাম (Main Focus) ────────────────────────────
            [
                'name'    => 'Chittagong',
                'bn_name' => 'চট্টগ্রাম',
                'upazilas' => [
                    // শহুরে থানা (City Thanas)
                    ['name' => 'Double Mooring',    'bn_name' => 'ডবলমুরিং'],
                    ['name' => 'Kotwali',            'bn_name' => 'কোতোয়ালী'],
                    ['name' => 'Panchlaish',         'bn_name' => 'পাঁচলাইশ'],
                    ['name' => 'Khulshi',            'bn_name' => 'খুলশী'],
                    ['name' => 'Halishahar',         'bn_name' => 'হালিশহর'],
                    ['name' => 'Chandgaon',          'bn_name' => 'চান্দগাঁও'],
                    ['name' => 'Bayazid Bostami',    'bn_name' => 'বায়েজিদ বোস্তামী'],
                    ['name' => 'Bakalia',            'bn_name' => 'বাকলিয়া'],
                    ['name' => 'Pahartali',          'bn_name' => 'পাহাড়তলী'],
                    ['name' => 'Bandar',             'bn_name' => 'বন্দর'],
                    ['name' => 'EPZ',                'bn_name' => 'ইপিজেড'],
                    // গ্রামীণ উপজেলা (Rural Upazilas)
                    ['name' => 'Sitakunda',          'bn_name' => 'সীতাকুণ্ড'],
                    ['name' => 'Mirsharai',          'bn_name' => 'মীরসরাই'],
                    ['name' => 'Sandwip',            'bn_name' => 'সন্দ্বীপ'],
                    ['name' => 'Fatikchhari',        'bn_name' => 'ফটিকছড়ি'],
                    ['name' => 'Hathazari',          'bn_name' => 'হাটহাজারী'],
                    ['name' => 'Raozan',             'bn_name' => 'রাউজান'],
                    ['name' => 'Rangunia',           'bn_name' => 'রাঙ্গুনিয়া'],
                    ['name' => 'Boalkhali',          'bn_name' => 'বোয়ালখালী'],
                    ['name' => 'Patiya',             'bn_name' => 'পটিয়া'],
                    ['name' => 'Chandanaish',        'bn_name' => 'চন্দনাইশ'],
                    ['name' => 'Satkania',           'bn_name' => 'সাতকানিয়া'],
                    ['name' => 'Lohagara',           'bn_name' => 'লোহাগাড়া'],
                    ['name' => 'Anwara',             'bn_name' => 'আনোয়ারা'],
                ],
            ],

            // ── কক্সবাজার ─────────────────────────────────────────
            [
                'name'    => "Cox's Bazar",
                'bn_name' => 'কক্সবাজার',
                'upazilas' => [
                    ['name' => "Cox's Bazar Sadar", 'bn_name' => 'কক্সবাজার সদর'],
                    ['name' => 'Chakaria',           'bn_name' => 'চকরিয়া'],
                    ['name' => 'Teknaf',             'bn_name' => 'টেকনাফ'],
                    ['name' => 'Ukhia',              'bn_name' => 'উখিয়া'],
                    ['name' => 'Ramu',               'bn_name' => 'রামু'],
                    ['name' => 'Moheshkhali',        'bn_name' => 'মহেশখালী'],
                ],
            ],

            // ── কুমিল্লা ───────────────────────────────────────────
            [
                'name'    => 'Comilla',
                'bn_name' => 'কুমিল্লা',
                'upazilas' => [
                    ['name' => 'Comilla Sadar',      'bn_name' => 'কুমিল্লা সদর'],
                    ['name' => 'Barura',             'bn_name' => 'বরুড়া'],
                    ['name' => 'Chandina',           'bn_name' => 'চান্দিনা'],
                    ['name' => 'Chauddagram',        'bn_name' => 'চৌদ্দগ্রাম'],
                    ['name' => 'Laksam',             'bn_name' => 'লাকসাম'],
                ],
            ],

            // ── ফেনী ───────────────────────────────────────────────
            [
                'name'    => 'Feni',
                'bn_name' => 'ফেনী',
                'upazilas' => [
                    ['name' => 'Feni Sadar',         'bn_name' => 'ফেনী সদর'],
                    ['name' => 'Chhagalnaiya',       'bn_name' => 'ছাগলনাইয়া'],
                    ['name' => 'Sonagazi',           'bn_name' => 'সোনাগাজী'],
                    ['name' => 'Daganbhuiyan',       'bn_name' => 'দাগনভূঁইয়া'],
                ],
            ],
        ];

        foreach ($data as $districtInfo) {
            // জেলা তৈরি করো
            $district = District::firstOrCreate(
                ['name' => $districtInfo['name']],
                ['bn_name' => $districtInfo['bn_name']]
            );

            // সেই জেলার সব উপজেলা তৈরি করো
            foreach ($districtInfo['upazilas'] as $upazilaInfo) {
                Upazila::firstOrCreate(
                    [
                        'district_id' => $district->id,
                        'name'        => $upazilaInfo['name'],
                    ],
                    ['bn_name' => $upazilaInfo['bn_name']]
                );
            }
        }

        $this->command->info('✅ জেলা ও উপজেলা seed হয়েছে।');
    }

    // ══════════════════════════════════════════════════════════════
    // ৩. Users Seed (Admin + Sample Donors)
    // ══════════════════════════════════════════════════════════════
    private function seedUsers(): void
    {
        // ── Reference data আনো ──────────────────────────────────
        // pluck('id', 'name') → ['A+' => 1, 'B+' => 3, ...] format-এ আনে
        $bloodGroups = BloodGroup::pluck('id', 'name');
        $chittagong  = District::where('name', 'Chittagong')->first();
        $upazilas    = Upazila::where('district_id', $chittagong->id)
                              ->pluck('id', 'name');

        // ── Admin Account ────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@digitalbloodconnect.bd'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'phone'    => '01700000000',
            ]
        );

        // ── Sample Donors ────────────────────────────────────────
        // এই donors গুলো search feature test করার জন্য
        $donors = [
            [
                'name'               => 'Rahim Uddin',
                'email'              => 'rahim@example.com',
                'phone'              => '01811111111',
                'blood_group_id'     => $bloodGroups['A+'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Sitakunda'] ?? null,
                'last_donation_date' => '2024-01-15',
                'is_available'       => true,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Karim Ahmed',
                'email'              => 'karim@example.com',
                'phone'              => '01822222222',
                'blood_group_id'     => $bloodGroups['B+'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Double Mooring'] ?? null,
                'last_donation_date' => '2023-10-20',
                'is_available'       => true,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Nusrat Jahan',
                'email'              => 'nusrat@example.com',
                'phone'              => '01833333333',
                'blood_group_id'     => $bloodGroups['O+'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Sitakunda'] ?? null,
                // সম্প্রতি donate করেছেন — not available
                'last_donation_date' => now()->subDays(30)->format('Y-m-d'),
                'is_available'       => false,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Farhan Hossain',
                'email'              => 'farhan@example.com',
                'phone'              => '01844444444',
                'blood_group_id'     => $bloodGroups['AB+'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Halishahar'] ?? null,
                'last_donation_date' => null,  // কখনো donate করেননি
                'is_available'       => true,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Sumaiya Begum',
                'email'              => 'sumaiya@example.com',
                'phone'              => '01855555555',
                'blood_group_id'     => $bloodGroups['O-'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Sitakunda'] ?? null,
                'last_donation_date' => '2023-09-05',
                'is_available'       => true,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Tarek Mia',
                'email'              => 'tarek@example.com',
                'phone'              => '01866666666',
                'blood_group_id'     => $bloodGroups['A-'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Mirsharai'] ?? null,
                'last_donation_date' => '2024-02-20',
                'is_available'       => true,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Rima Akter',
                'email'              => 'rima@example.com',
                'phone'              => '01877777777',
                'blood_group_id'     => $bloodGroups['B-'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Chandgaon'] ?? null,
                'last_donation_date' => null,
                'is_available'       => true,
                'role'               => 'donor',
            ],
            [
                'name'               => 'Sohel Rana',
                'email'              => 'sohel@example.com',
                'phone'              => '01888888888',
                'blood_group_id'     => $bloodGroups['AB-'],
                'district_id'        => $chittagong->id,
                'upazila_id'         => $upazilas['Kotwali'] ?? null,
                'last_donation_date' => '2023-12-01',
                'is_available'       => true,
                'role'               => 'donor',
            ],
        ];

        foreach ($donors as $donorData) {
            User::firstOrCreate(
                ['email' => $donorData['email']],
                array_merge($donorData, ['password' => Hash::make('password')])
            );
        }

        $this->command->info('✅ ১ Admin + ' . count($donors) . ' জন Sample Donor seed হয়েছে।');
        $this->command->line('   Login: admin@digitalbloodconnect.bd | password: admin123');
        $this->command->line('   Donor: rahim@example.com | password: password');
    }

    // ══════════════════════════════════════════════════════════════
    // ৪. Blood Requests Seed
    // ══════════════════════════════════════════════════════════════
    private function seedBloodRequests(): void
    {
        $rahim      = User::where('email', 'rahim@example.com')->first();
        $chittagong = District::where('name', 'Chittagong')->first();
        $sitakunda  = Upazila::where('name', 'Sitakunda')->first();
        $dblMooring = Upazila::where('name', 'Double Mooring')->first();

        $aPlus      = BloodGroup::where('name', 'A+')->first();
        $oMinus     = BloodGroup::where('name', 'O-')->first();
        $bPlus      = BloodGroup::where('name', 'B+')->first();

        if (! $rahim || ! $chittagong) {
            $this->command->warn('⚠️ Blood requests seed skip — users/districts not found.');
            return;
        }

        $requests = [
            [
                'user_id'        => $rahim->id,
                'blood_group_id' => $aPlus->id,
                'district_id'    => $chittagong->id,
                'upazila_id'     => $sitakunda?->id,
                'hospital_name'  => 'Chittagong Medical College Hospital',
                'address'        => 'K.B. Fazlul Kader Road, Chittagong',
                'needed_by'      => now()->addHours(6),
                'units_needed'   => 2,
                'contact_phone'  => '01811111111',
                'notes'          => 'Patient undergoing major surgery. Urgent need.',
                'status'         => 'open',
                'is_urgent'      => true,
            ],
            [
                'user_id'        => $rahim->id,
                'blood_group_id' => $oMinus->id,
                'district_id'    => $chittagong->id,
                'upazila_id'     => null,
                'hospital_name'  => 'Bangladesh Institute of Research and Rehabilitation (BIRDEM)',
                'address'        => 'Agrabad, Chittagong',
                'needed_by'      => now()->addDay(),
                'units_needed'   => 1,
                'contact_phone'  => '01899999999',
                'notes'          => 'O- is universal donor. Any O- donor please contact.',
                'status'         => 'open',
                'is_urgent'      => false,
            ],
            [
                'user_id'        => $rahim->id,
                'blood_group_id' => $bPlus->id,
                'district_id'    => $chittagong->id,
                'upazila_id'     => $sitakunda?->id,
                'hospital_name'  => 'Sitakunda Upazila Health Complex',
                'address'        => 'Sitakunda, Chittagong',
                'needed_by'      => now()->addHours(12),
                'units_needed'   => 3,
                'contact_phone'  => '01722334455',
                'notes'          => 'Accident victim needs blood immediately.',
                'status'         => 'open',
                'is_urgent'      => true,
            ],
        ];

        foreach ($requests as $reqData) {
            BloodRequest::create($reqData);
        }

        $this->command->info('✅ ' . count($requests) . ' টি Sample Blood Request seed হয়েছে।');
    }
}