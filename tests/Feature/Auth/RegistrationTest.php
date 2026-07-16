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
        'photo' => 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        'voter_card' => 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    ]);

    $this->assertDatabaseHas('members', [
        'first_name' => 'Test',
        'last_name' => 'Member',
        'phone' => '+2348012345678',
    ]);
    $response->assertRedirect(route('register.success'));
});

test('existing members can upload voter card via check-status', function () {
    $state = State::create(['name' => 'Zamfara', 'code' => 'ZA']);
    $lga = Lga::create(['state_id' => $state->id, 'name' => 'Maradun']);
    $ward = Ward::create(['lga_id' => $lga->id, 'name' => 'Maradun South']);

    $member = \App\Models\Member::create([
        'membership_number' => 'SAJ-2026-00000001',
        'first_name' => 'Old',
        'last_name' => 'Member',
        'gender' => 'Male',
        'dob' => '1985-05-05',
        'phone' => '+2348099999999',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'status' => 'pending',
        'registered_at' => now(),
    ]);

    $response = $this->post('/check-status/voter-card', [
        'phone' => '+2348099999999',
        'membership_number' => 'SAJ-2026-00000001',
        'voter_card' => 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertNotNull($member->fresh()->voter_card_path);
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
