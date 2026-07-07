<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\EscoOfficial;
use App\Models\Lga;
use App\Models\Member;
use App\Models\Position;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            PositionSeeder::class,
            LocationSeeder::class,
            MovementContentSeeder::class,
        ]);

        $kanoState = State::whereIn('name', ['Kano State', 'KANO'])->first();
        $kanoMunicipal = Lga::whereIn('name', ['Kano Municipal', 'KANO MUNICIPAL'])->first();
        $sharadaWard = Ward::whereIn('name', ['Sharada', 'SHARADA'])->first();

        // Super Admin
        $superAdmin = User::firstOrCreate(['email' => 'admin@shehiya.org'], [
            'name' => 'Shaihiyya Super Admin',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('Super Administrator');

        // State Coordinator (Kano)
        $stateCoordUser = User::firstOrCreate(['email' => 'kano@shehiya.org'], [
            'name' => 'Alhaji Musa Kano',
            'password' => bcrypt('password'),
            'state_id' => $kanoState?->id,
        ]);
        $stateCoordUser->assignRole('State Coordinator');

        // Ward Coordinator (Sharada)
        $wardCoordUser = User::firstOrCreate(['email' => 'sharada@shehiya.org'], [
            'name' => 'Usman Sharada',
            'password' => bcrypt('password'),
            'state_id' => $kanoState?->id,
            'lga_id' => $kanoMunicipal?->id,
            'ward_id' => $sharadaWard?->id,
        ]);
        $wardCoordUser->assignRole('Ward Coordinator');

        // Sample Members
        if ($kanoState && $kanoMunicipal && $sharadaWard) {
            $member1 = Member::firstOrCreate(['membership_number' => 'SA-2026-00001'], [
                'first_name' => 'Abubakar',
                'last_name' => 'Sadiq',
                'gender' => 'Male',
                'dob' => '1985-05-12',
                'phone' => '08031234567',
                'email' => 'sadiq@example.com',
                'occupation' => 'Civil Servant',
                'state_id' => $kanoState->id,
                'lga_id' => $kanoMunicipal->id,
                'ward_id' => $sharadaWard->id,
                'status' => 'verified',
            ]);

            $member2 = Member::firstOrCreate(['membership_number' => 'SA-2026-00002'], [
                'first_name' => 'Fatima',
                'last_name' => 'Bello',
                'gender' => 'Female',
                'dob' => '1992-11-20',
                'phone' => '08059876543',
                'email' => 'fatima@example.com',
                'occupation' => 'Teacher',
                'state_id' => $kanoState->id,
                'lga_id' => $kanoMunicipal->id,
                'ward_id' => $sharadaWard->id,
                'status' => 'pending',
            ]);

            $stateCoordPos = Position::where('name', 'State Coordinator')->first();
            $nationalChairmanPos = Position::where('name', 'National Chairman')->first();

            // Sample EXCO Officials
            EscoOfficial::firstOrCreate(['email' => 'kano@shehiya.org'], [
                'member_id' => $member1->id,
                'user_id' => $stateCoordUser->id,
                'full_name' => 'Alhaji Musa Kano',
                'position_id' => $stateCoordPos?->id,
                'phone' => '08031234567',
                'state_id' => $kanoState->id,
                'appointed_at' => '2026-01-01',
                'status' => 'active',
            ]);

            EscoOfficial::firstOrCreate(['email' => 'chairman@shehiya.org'], [
                'full_name' => 'Dr. Ibrahim Shaihiyya',
                'position_id' => $nationalChairmanPos?->id,
                'phone' => '08022223333',
                'appointed_at' => '2025-06-01',
                'status' => 'active',
            ]);

            // Sample Announcements
            Announcement::firstOrCreate(['title' => 'Welcome to Shaihiyya Amanar Jagora Platform'], [
                'content' => 'All coordinators are requested to begin registering members across their respective wards immediately.',
                'type' => 'notice',
                'published_by_user_id' => $superAdmin->id,
                'target_level' => 'national',
            ]);

            Announcement::firstOrCreate(['title' => 'Kano State EXCO Strategy Meeting'], [
                'content' => 'An emergency strategy meeting will hold at the Kano state secretariat next Saturday at 10 AM.',
                'type' => 'meeting',
                'published_by_user_id' => $stateCoordUser->id,
                'target_level' => 'state',
                'state_id' => $kanoState->id,
            ]);
        }
    }
}
