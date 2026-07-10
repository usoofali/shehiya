<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EscoController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberVerificationController;
use App\Http\Controllers\MovementContentController;
use App\Http\Controllers\PatronController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('/announcements/{announcement}', [WelcomeController::class, 'showAnnouncement'])->name('announcements.show');

// Member self-registration success page (public)
Route::get('/register/success', function () {
    return Inertia::render('auth/RegisterSuccess');
})->name('register.success');

// Public Member Check & Verification
use App\Http\Controllers\PublicMemberController;

Route::get('/check-status', [PublicMemberController::class, 'showCheckStatus'])->name('status.check');
Route::post('/check-status', [PublicMemberController::class, 'checkStatus']);
Route::get('/badge/{membership_number}', [PublicMemberController::class, 'showBadge'])->name('badge.show');
Route::get('/verify/{membership_number}', [PublicMemberController::class, 'verify'])->name('badge.verify');

// Public Location Data API (used by both public registration and admin member forms)
Route::get('api/wards/{ward}/polling-units', [MemberController::class, 'pollingUnits'])->name('api.wards.polling-units');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Member Directory & Registration
    Route::resource('members', MemberController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('members/{member}/verify', [MemberVerificationController::class, 'store'])->name('members.verify');

    // EXCO Leadership
    Route::resource('esco', EscoController::class)->only(['index', 'store', 'destroy']);

    // Organization Patrons & Royal Leadership
    Route::resource('patrons', PatronController::class)->only(['index', 'store', 'update', 'destroy']);

    // Announcements
    Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);

    // Super Admin Content Management
    Route::resource('admin/content', MovementContentController::class)->only(['index', 'store', 'update', 'destroy'])->names('admin.content');

    // Super Admin: Coordinator User Management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
