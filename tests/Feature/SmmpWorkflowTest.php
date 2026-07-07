<?php

use App\Models\Lga;
use App\Models\Member;
use App\Models\State;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('enforces jurisdiction scoping for members directory', function () {
    $stateA = State::create(['name' => 'Kano', 'code' => 'KN']);
    $stateB = State::create(['name' => 'Lagos', 'code' => 'LA']);

    $lgaA = Lga::create(['state_id' => $stateA->id, 'name' => 'Dala']);
    $lgaB = Lga::create(['state_id' => $stateB->id, 'name' => 'Ikeja']);

    $wardA = Ward::create(['lga_id' => $lgaA->id, 'name' => 'Gwammaja']);
    $wardB = Ward::create(['lga_id' => $lgaB->id, 'name' => 'Allen']);

    $coordinatorA = User::factory()->create([
        'state_id' => $stateA->id,
    ]);
    $coordinatorA->assignRole('State Coordinator');

    $memberA = Member::create([
        'membership_number' => 'SA-2026-00001',
        'first_name' => 'Musa',
        'last_name' => 'Ibrahim',
        'gender' => 'Male',
        'dob' => '1990-01-01',
        'phone' => '08011111111',
        'state_id' => $stateA->id,
        'lga_id' => $lgaA->id,
        'ward_id' => $wardA->id,
        'status' => 'pending',
        'registered_at' => now(),
    ]);

    $memberB = Member::create([
        'membership_number' => 'SA-2026-00002',
        'first_name' => 'Tunde',
        'last_name' => 'Bakare',
        'gender' => 'Male',
        'dob' => '1992-05-15',
        'phone' => '08022222222',
        'state_id' => $stateB->id,
        'lga_id' => $lgaB->id,
        'ward_id' => $wardB->id,
        'status' => 'pending',
        'registered_at' => now(),
    ]);

    $response = $this->actingAs($coordinatorA)->get('/members');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Members/Index')
        ->has('members.data', 1)
        ->where('members.data.0.membership_number', 'SA-2026-00001')
    );
});

test('allows member verification and creates audit log', function () {
    $state = State::create(['name' => 'Kaduna', 'code' => 'KD']);
    $lga = Lga::create(['state_id' => $state->id, 'name' => 'Zaria']);
    $ward = Ward::create(['lga_id' => $lga->id, 'name' => 'Tudun Wada']);

    $admin = User::factory()->create([
        'state_id' => $state->id,
    ]);
    $admin->assignRole('State Coordinator');

    $member = Member::create([
        'membership_number' => 'SA-2026-00003',
        'first_name' => 'Fatima',
        'last_name' => 'Aliyu',
        'gender' => 'Female',
        'dob' => '1995-08-20',
        'phone' => '08033333333',
        'state_id' => $state->id,
        'lga_id' => $lga->id,
        'ward_id' => $ward->id,
        'status' => 'pending',
        'registered_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post('/members/'.$member->id.'/verify', [
        'new_status' => 'verified',
        'comments' => 'Documents authenticated successfully.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'status' => 'verified',
    ]);

    $this->assertDatabaseHas('member_verifications', [
        'member_id' => $member->id,
        'verified_by_user_id' => $admin->id,
        'previous_status' => 'pending',
        'new_status' => 'verified',
        'comments' => 'Documents authenticated successfully.',
    ]);
});
