<?php

namespace Database\Seeders;

use App\Models\Lga;
use App\Models\State;
use App\Models\Ward;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hierarchy = [
            'Kano State' => [
                'code' => 'KN',
                'lgas' => [
                    'Kano Municipal' => ['Sharada', 'Tudun Wazichi', 'Zango', 'Kofar Wambai'],
                    'Dala' => ['Gwammaja', 'Kabuwaya', 'Adada', 'Dogon Nama'],
                    'Gwale' => ['Gwale Main', 'Gyadi-Gyadi', 'Goron Dutse', 'Diso'],
                ],
            ],
            'FCT Abuja' => [
                'code' => 'FC',
                'lgas' => [
                    'Abuja Municipal (AMAC)' => ['Wuse', 'Garki', 'Maitama', 'Asokoro'],
                    'Bwari' => ['Bwari Central', 'Kubwa', 'Ushafa', 'Dutse'],
                ],
            ],
            'Lagos State' => [
                'code' => 'LA',
                'lgas' => [
                    'Ikeja' => ['Alausa', 'Anifowoshe', 'GRA', 'Ojodu'],
                    'Lagos Island' => ['Epetedo', 'Olowogbowo', 'Isale Eko'],
                ],
            ],
        ];

        foreach ($hierarchy as $stateName => $stateData) {
            $state = State::create([
                'name' => $stateName,
                'code' => $stateData['code'],
            ]);

            foreach ($stateData['lgas'] as $lgaName => $wards) {
                $lga = Lga::create([
                    'state_id' => $state->id,
                    'name' => $lgaName,
                ]);

                foreach ($wards as $wardName) {
                    Ward::create([
                        'lga_id' => $lga->id,
                        'name' => $wardName,
                    ]);
                }
            }
        }
    }
}
