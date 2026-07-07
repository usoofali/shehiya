<?php

namespace Database\Seeders;

use App\Models\Lga;
use App\Models\PollingUnit;
use App\Models\State;
use App\Models\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');

        $jsonPath = base_path('inec_all_polling_units.json');

        if (! File::exists($jsonPath)) {
            $this->command?->warn("File {$jsonPath} not found.");

            return;
        }

        $this->command?->info('Loading INEC polling units data from JSON...');
        $data = json_decode(File::get($jsonPath), true);

        if (! is_array($data)) {
            $this->command?->error("Invalid JSON data in {$jsonPath}.");

            return;
        }

        $pollingUnitsChunk = [];
        $now = now();

        $statesMap = State::pluck('id', 'name')->toArray();

        $lgasMap = [];
        foreach (Lga::select('id', 'state_id', 'name')->cursor() as $lga) {
            $lgasMap[$lga->state_id.'_'.$lga->name] = $lga->id;
        }

        $wardsMap = [];
        foreach (Ward::select('id', 'lga_id', 'name')->cursor() as $ward) {
            $wardsMap[$ward->lga_id.'_'.$ward->name] = $ward->id;
        }

        foreach ($data as $stateData) {
            $stateName = trim($stateData['name'] ?? '');
            if (empty($stateName)) {
                continue;
            }

            if (! isset($statesMap[$stateName])) {
                $state = State::create([
                    'name' => $stateName,
                    'code' => $stateData['code'] ?? null,
                ]);
                $statesMap[$stateName] = $state->id;
            }
            $stateId = $statesMap[$stateName];

            $lgas = $stateData['lgas'] ?? [];
            foreach ($lgas as $lgaData) {
                $lgaName = trim($lgaData['name'] ?? '');
                if (empty($lgaName)) {
                    continue;
                }

                $lgaKey = $stateId.'_'.$lgaName;
                if (! isset($lgasMap[$lgaKey])) {
                    $lga = Lga::create([
                        'state_id' => $stateId,
                        'name' => $lgaName,
                    ]);
                    $lgasMap[$lgaKey] = $lga->id;
                }
                $lgaId = $lgasMap[$lgaKey];

                $wards = $lgaData['wards'] ?? [];
                foreach ($wards as $wardData) {
                    $wardName = trim($wardData['name'] ?? '');
                    if (empty($wardName)) {
                        continue;
                    }

                    $wardKey = $lgaId.'_'.$wardName;
                    if (! isset($wardsMap[$wardKey])) {
                        $ward = Ward::create([
                            'lga_id' => $lgaId,
                            'name' => $wardName,
                        ]);
                        $wardsMap[$wardKey] = $ward->id;
                    }
                    $wardId = $wardsMap[$wardKey];

                    $pollingUnits = $wardData['polling_units'] ?? [];
                    $seenNamesInWard = [];

                    foreach ($pollingUnits as $puData) {
                        $puName = trim($puData['name'] ?? '');
                        if (empty($puName)) {
                            continue;
                        }

                        if (isset($seenNamesInWard[$puName])) {
                            $codeSuffix = ! empty($puData['code']) ? $puData['code'] : count($seenNamesInWard);
                            $puName = $puName.' ('.$codeSuffix.')';
                            $counter = 2;
                            $baseName = $puName;
                            while (isset($seenNamesInWard[$puName])) {
                                $puName = $baseName.' #'.$counter;
                                $counter++;
                            }
                        }
                        $seenNamesInWard[$puName] = true;

                        $pollingUnitsChunk[] = [
                            'ward_id' => $wardId,
                            'name' => $puName,
                            'code' => $puData['code'] ?? null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($pollingUnitsChunk) >= 1000) {
                            PollingUnit::upsert($pollingUnitsChunk, ['ward_id', 'name'], ['code', 'updated_at']);
                            $pollingUnitsChunk = [];
                        }
                    }
                }
            }
        }

        if (! empty($pollingUnitsChunk)) {
            PollingUnit::upsert($pollingUnitsChunk, ['ward_id', 'name'], ['code', 'updated_at']);
        }
    }
}
