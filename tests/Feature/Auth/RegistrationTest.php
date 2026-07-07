<?php

use App\Models\Lga;
use App\Models\PollingUnit;
use App\Models\State;
use App\Models\Ward;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new members can register', function () {
    $state = State::create(['name' => 'Zamfara', 'code' => 'ZA']);
    $lga = Lga::create(['state_id' => $state->id, 'name' => 'Maradun']);
    $ward = Ward::create(['lga_id' => $lga->id, 'name' => 'Maradun South']);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'Member',
        'gender' => 'Male',
        'dob' => '1990-01-01',
        'phone' => '+2348012345678',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
    ]);

    $this->assertDatabaseHas('members', [
        'first_name' => 'Test',
        'last_name' => 'Member',
        'phone' => '+2348012345678',
    ]);
    $response->assertRedirect(route('register.success'));
});

test('guests can fetch polling units for a ward during registration', function () {
    $state = State::create(['name' => 'Zamfara', 'code' => 'ZA']);
    $lga = Lga::create(['state_id' => $state->id, 'name' => 'Maradun']);
    $ward = Ward::create(['lga_id' => $lga->id, 'name' => 'Maradun South']);
    $pu = PollingUnit::create(['ward_id' => $ward->id, 'name' => 'PU 001', 'code' => '001']);

    $response = $this->getJson("/api/wards/{$ward->id}/polling-units");

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'PU 001', 'code' => '001']);
});
