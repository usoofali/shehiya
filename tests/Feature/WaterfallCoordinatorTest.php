<?php

use App\Models\Lga;
use App\Models\Member;
use App\Models\PollingUnit;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('state coordinator can create lga coordinator in same state', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);

    $stateCoordinator = User::factory()->create(['state_id' => $state->id]);
    $stateCoordinator->assignRole('State Coordinator');

    $response = $this->actingAs($stateCoordinator)->post('/admin/users', [
        'name' => 'LGA Admin',
        'email' => 'lga@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'LGA Coordinator',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'lga@example.com',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
    ]);
});

test('state coordinator cannot create ward coordinator directly', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);

    $stateCoordinator = User::factory()->create(['state_id' => $state->id]);
    $stateCoordinator->assignRole('State Coordinator');

    $response = $this->actingAs($stateCoordinator)->post('/admin/users', [
        'name' => 'Ward Admin',
        'email' => 'ward@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'Ward Coordinator',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
    ]);

    $response->assertForbidden();
});

test('ward coordinator can create polling unit coordinator in same ward', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);
    $pollingUnit = PollingUnit::create(['name' => 'PU 001', 'ward_id' => $ward->id]);

    $wardCoordinator = User::factory()->create([
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
    ]);
    $wardCoordinator->assignRole('Ward Coordinator');

    $response = $this->actingAs($wardCoordinator)->post('/admin/users', [
        'name' => 'PU Admin',
        'email' => 'pu@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'Polling Unit Coordinator',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'polling_unit_id' => $pollingUnit->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'pu@example.com',
        'polling_unit_id' => $pollingUnit->id,
    ]);
});

test('member scope accessible by filters by polling unit id for polling unit coordinator', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);
    $pu1 = PollingUnit::create(['name' => 'PU 001', 'ward_id' => $ward->id]);
    $pu2 = PollingUnit::create(['name' => 'PU 002', 'ward_id' => $ward->id]);

    $puCoordinator = User::factory()->create([
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'polling_unit_id' => $pu1->id,
    ]);
    $puCoordinator->assignRole('Polling Unit Coordinator');

    $member1 = Member::create([
        'membership_number' => 'SA-2026-00010',
        'first_name' => 'Musa',
        'last_name' => 'Ibrahim',
        'gender' => 'Male',
        'dob' => '1990-01-01',
        'phone' => '08011111111',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'polling_unit_id' => $pu1->id,
        'status' => 'pending',
        'registered_at' => now(),
    ]);

    $member2 = Member::create([
        'membership_number' => 'SA-2026-00011',
        'first_name' => 'Tunde',
        'last_name' => 'Bakare',
        'gender' => 'Male',
        'dob' => '1992-05-15',
        'phone' => '08022222222',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'polling_unit_id' => $pu2->id,
        'status' => 'pending',
        'registered_at' => now(),
    ]);

    $accessibleMembers = Member::accessibleBy($puCoordinator)->get();

    expect($accessibleMembers->pluck('id'))->toContain($member1->id)
        ->not->toContain($member2->id);
});
