<?php

use App\Models\Lga;
use App\Models\PollingUnit;
use App\Models\State;
use App\Models\Ward;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('location seeder populates state, lga, ward, and polling unit tables from json', function () {
    $this->seed(LocationSeeder::class);

    expect(State::count())->toBeGreaterThan(30);
    expect(Lga::count())->toBeGreaterThan(700);
    expect(Ward::count())->toBeGreaterThan(8000);
    expect(PollingUnit::count())->toBeGreaterThan(100000);

    $state = State::where('name', 'ABIA')->first();
    expect($state)->not->toBeNull();
    expect($state->code)->toBe('01');

    $lga = Lga::where('state_id', $state->id)->where('name', 'ABA NORTH')->first();
    expect($lga)->not->toBeNull();

    $ward = Ward::where('lga_id', $lga->id)->where('name', 'EZIAMA')->first();
    expect($ward)->not->toBeNull();

    $pu = PollingUnit::where('ward_id', $ward->id)->where('name', 'like', '%RAILWAY QUARTERS%')->first();
    expect($pu)->not->toBeNull();
});
