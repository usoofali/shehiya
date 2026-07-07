<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            'National Chairman',
            'State Coordinator',
            'LGA Coordinator',
            'Ward Coordinator',
            'Coordinator',
            'Deputy Coordinator',
            'Secretary',
            'Secretary General',
            'Assistant Secretary General',
            'Organising Secretary',
            'Assistant Organising Secretary',
            'Public Relations Officer',
            'Publicity Secretary',
            'Assistant Publicity Secretary',
            'Director Contacts and Mobilisation',
            'Assistant Director, Contacts and Mobilisation',
            'Welfare Secretary',
            'Assistant Welfare Secretary',
            'Financial Secretary',
            'Treasurer',
            'State Auditor General',
            'Legal Adviser',
            'Women Leader',
            'Youth Leader',
            'State Youth Leader',
            'Assistant Youth Leader',
            'Zonal Coordinator (West)',
            'Zonal Coordinator (Central)',
            'Zonal Coordinator (North)',
            'Zonal Coordinator (East)',
            'Zonal Coordinator (South)',
            'Vice Chairman',
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['name' => $position]);
        }
    }
}
