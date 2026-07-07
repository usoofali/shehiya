<?php

namespace App\Console\Commands;

use App\Models\PollingUnit;
use App\Models\Ward;
use Illuminate\Console\Command;

class SyncInecLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smmp:sync-inec {--ward= : INEC Ward ID to scrape polling units for} {--seed-default : Populate sample polling units for all existing DB wards}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape polling units from INEC CVR portal via Python script or seed defaults';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('seed-default')) {
            $this->info('Seeding default polling units for existing database wards...');
            $wards = Ward::all();
            $count = 0;
            foreach ($wards as $ward) {
                $pus = [
                    ['code' => '001', 'name' => "{$ward->name} Central Primary School"],
                    ['code' => '002', 'name' => "{$ward->name} Health Centre Gate"],
                    ['code' => '003', 'name' => "{$ward->name} Town Hall Open Space"],
                    ['code' => '004', 'name' => "{$ward->name} Market Square"],
                ];
                foreach ($pus as $pu) {
                    PollingUnit::firstOrCreate(
                        ['ward_id' => $ward->id, 'name' => $pu['name']],
                        ['code' => $pu['code']]
                    );
                    $count++;
                }
            }
            $this->info("Successfully seeded {$count} polling units across {$wards->count()} wards.");

            return Command::SUCCESS;
        }

        $wardId = $this->option('ward') ?: 1;
        $this->info("Invoking Python scraper for INEC Ward ID: {$wardId}...");

        $scriptPath = base_path('scripts/scrape_inec_pu.py');
        $output = shell_exec("python3 {$scriptPath} --ward={$wardId}");

        if (! $output) {
            $this->error('Failed to retrieve data from Python scraper.');

            return Command::FAILURE;
        }

        $data = json_decode($output, true);
        if (! is_array($data)) {
            $this->error('Invalid JSON returned from scraper.');

            return Command::FAILURE;
        }

        // Ensure target ward exists in our DB or attach to first ward for demo
        $ward = Ward::find($wardId) ?: Ward::first();
        if (! $ward) {
            $this->error('No ward found in database to attach scraped polling units.');

            return Command::FAILURE;
        }

        $count = 0;
        foreach ($data as $item) {
            if (! empty($item['name'])) {
                PollingUnit::updateOrCreate(
                    ['ward_id' => $ward->id, 'name' => $item['name']],
                    ['code' => $item['code'] ?? null]
                );
                $count++;
            }
        }

        $this->info("Successfully synced {$count} Polling Units from INEC to Ward '{$ward->name}' (ID: {$ward->id}).");

        return Command::SUCCESS;
    }
}
