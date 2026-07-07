<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Super Admin
        $superAdmin = User::firstOrCreate(['email' => 'admin@shehiya.org'], [
            'name' => 'Shaihiyya Super Admin',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('Super Administrator');
    }
}
