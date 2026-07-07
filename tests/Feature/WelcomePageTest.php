<?php

use App\Models\Announcement;
use App\Models\MovementContent;
use App\Models\User;
use Database\Seeders\MovementContentSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('public welcome page can be rendered with dynamic contents', function () {
    MovementContent::create([
        'key' => 'hero_title',
        'title' => 'Test Hero Title',
        'body' => 'Test Body',
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
});

test('movement content seeder populates vision and mission sections', function () {
    $this->seed(MovementContentSeeder::class);

    $this->assertDatabaseHas('movement_contents', [
        'key' => 'vision_section',
        'title' => 'Vision of the ORGANIZATION',
    ]);

    $this->assertDatabaseHas('movement_contents', [
        'key' => 'mission_section',
        'title' => 'Mission of the ORGANIZATION',
    ]);
});

test('super administrator can create website content blocks', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Administrator');

    $response = $this->actingAs($admin)->post('/admin/content', [
        'key' => 'custom_section',
        'title' => 'Custom Title',
        'body' => 'Custom Body Content',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('movement_contents', [
        'key' => 'custom_section',
        'title' => 'Custom Title',
    ]);
});

test('non super administrator cannot manage website content', function () {
    $coordinator = User::factory()->create();
    $coordinator->assignRole('State Coordinator');

    $response = $this->actingAs($coordinator)->post('/admin/content', [
        'key' => 'hacked_section',
        'title' => 'Hacked Title',
    ]);

    $response->assertStatus(403);
});

test('welcome page displays latest announcements regardless of target level when unfiltered', function () {
    $user = User::factory()->create();
    Announcement::create([
        'title' => 'State Level Notice',
        'content' => 'This is a state level notice.',
        'type' => 'notice',
        'target_level' => 'state',
        'published_by_user_id' => $user->id,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('announcements', 1)
        );
});
