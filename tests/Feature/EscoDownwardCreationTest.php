<?php

use App\Models\EscoOfficial;
use App\Models\Lga;
use App\Models\PollingUnit;
use App\Models\Position;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('state coordinator can appoint state lga ward and polling unit escos within their state', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);
    $pollingUnit = PollingUnit::create(['name' => 'PU 001', 'ward_id' => $ward->id]);
    $position = Position::create(['name' => 'Chairman']);

    $stateCoordinator = User::factory()->create(['state_id' => $state->id]);
    $stateCoordinator->assignRole('State Coordinator');

    // Appoint State ESCO
    $response = $this->actingAs($stateCoordinator)->post('/esco', [
        'full_name' => 'State Chairman',
        'position_id' => $position->id,
        'phone' => '08011111111',
        'state_id' => $state->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('esco_officials', ['full_name' => 'State Chairman', 'state_id' => $state->id]);

    // Appoint Polling Unit ESCO downwards
    $response2 = $this->actingAs($stateCoordinator)->post('/esco', [
        'full_name' => 'PU Chairman',
        'position_id' => $position->id,
        'phone' => '08022222222',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'polling_unit_id' => $pollingUnit->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response2->assertRedirect();
    $this->assertDatabaseHas('esco_officials', ['full_name' => 'PU Chairman', 'polling_unit_id' => $pollingUnit->id]);
});

test('state coordinator cannot appoint national esco or esco in another state', function () {
    $state1 = State::create(['name' => 'Kano', 'code' => 'KN']);
    $state2 = State::create(['name' => 'Lagos', 'code' => 'LA']);
    $position = Position::create(['name' => 'Secretary']);

    $stateCoordinator = User::factory()->create(['state_id' => $state1->id]);
    $stateCoordinator->assignRole('State Coordinator');

    // Attempt National ESCO (empty state_id)
    $response1 = $this->actingAs($stateCoordinator)->post('/esco', [
        'full_name' => 'National Sec',
        'position_id' => $position->id,
        'phone' => '08033333333',
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response1->assertForbidden();

    // Attempt ESCO in another state
    $response2 = $this->actingAs($stateCoordinator)->post('/esco', [
        'full_name' => 'Lagos Sec',
        'position_id' => $position->id,
        'phone' => '08044444444',
        'state_id' => $state2->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response2->assertForbidden();
});

test('lga coordinator can appoint lga ward and polling unit escos within their lga but not state escos', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);
    $pollingUnit = PollingUnit::create(['name' => 'PU 001', 'ward_id' => $ward->id]);
    $position = Position::create(['name' => 'Youth Leader']);

    $lgaCoordinator = User::factory()->create(['state_id' => $state->id, 'lga_id' => $lga->id]);
    $lgaCoordinator->assignRole('LGA Coordinator');

    // Appoint LGA ESCO
    $response1 = $this->actingAs($lgaCoordinator)->post('/esco', [
        'full_name' => 'LGA Youth Leader',
        'position_id' => $position->id,
        'phone' => '08055555555',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response1->assertRedirect();
    $this->assertDatabaseHas('esco_officials', ['full_name' => 'LGA Youth Leader', 'lga_id' => $lga->id]);

    // Appoint PU ESCO downwards
    $response2 = $this->actingAs($lgaCoordinator)->post('/esco', [
        'full_name' => 'PU Youth Leader',
        'position_id' => $position->id,
        'phone' => '08066666666',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'polling_unit_id' => $pollingUnit->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response2->assertRedirect();

    // Attempt State ESCO (empty lga_id)
    $response3 = $this->actingAs($lgaCoordinator)->post('/esco', [
        'full_name' => 'State Youth Leader',
        'position_id' => $position->id,
        'phone' => '08077777777',
        'state_id' => $state->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response3->assertForbidden();
});

test('ward coordinator can appoint ward and polling unit escos within their ward only', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward1 = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);
    $ward2 = Ward::create(['name' => 'Kantudu', 'lga_id' => $lga->id]);
    $pollingUnit = PollingUnit::create(['name' => 'PU 001', 'ward_id' => $ward1->id]);
    $position = Position::create(['name' => 'Women Leader']);

    $wardCoordinator = User::factory()->create(['state_id' => $state->id, 'lga_id' => $lga->id, 'ward_id' => $ward1->id]);
    $wardCoordinator->assignRole('Ward Coordinator');

    // Appoint Ward ESCO
    $response1 = $this->actingAs($wardCoordinator)->post('/esco', [
        'full_name' => 'Ward Women Leader',
        'position_id' => $position->id,
        'phone' => '08088888888',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward1->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response1->assertRedirect();

    // Appoint PU ESCO downwards
    $response2 = $this->actingAs($wardCoordinator)->post('/esco', [
        'full_name' => 'PU Women Leader',
        'position_id' => $position->id,
        'phone' => '08099999999',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward1->id,
        'polling_unit_id' => $pollingUnit->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response2->assertRedirect();

    // Attempt ESCO in another ward
    $response3 = $this->actingAs($wardCoordinator)->post('/esco', [
        'full_name' => 'Other Ward Leader',
        'position_id' => $position->id,
        'phone' => '08000000000',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward2->id,
        'appointed_at' => '2026-07-01',
        'status' => 'active',
    ]);
    $response3->assertForbidden();
});

test('coordinators can only delete escos within their downward jurisdiction', function () {
    $state = State::create(['name' => 'Kano', 'code' => 'KN']);
    $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);
    $ward = Ward::create(['name' => 'Gabuwa', 'lga_id' => $lga->id]);
    $position = Position::create(['name' => 'PRO']);

    $nationalEsco = EscoOfficial::create(['full_name' => 'Nat PRO', 'position_id' => $position->id, 'phone' => '111', 'appointed_at' => '2026-01-01', 'status' => 'active']);
    $wardEsco = EscoOfficial::create(['full_name' => 'Ward PRO', 'position_id' => $position->id, 'phone' => '222', 'state_id' => $state->id, 'lga_id' => $lga->id, 'ward_id' => $ward->id, 'appointed_at' => '2026-01-01', 'status' => 'active']);

    $stateCoordinator = User::factory()->create(['state_id' => $state->id]);
    $stateCoordinator->assignRole('State Coordinator');

    // State Coordinator cannot delete National ESCO
    $response1 = $this->actingAs($stateCoordinator)->delete("/esco/{$nationalEsco->id}");
    $response1->assertForbidden();
    $this->assertDatabaseHas('esco_officials', ['id' => $nationalEsco->id]);

    // State Coordinator CAN delete Ward ESCO in their state
    $response2 = $this->actingAs($stateCoordinator)->delete("/esco/{$wardEsco->id}");
    $response2->assertRedirect();
    $this->assertDatabaseMissing('esco_officials', ['id' => $wardEsco->id]);
});
