<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ShortUrlController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Public short url redirect
Route::get('/s/{code}', [ShortUrlController::class, 'redirect'])->name('short-urls.redirect');


Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class,'showLogin'])->name('login');
    Route::post('/login', [LoginController::class,'login'])->name('login.submit');

    // Accept invitation and set password
    Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

});


Route::middleware('auth')->group(function () {

    Route::get('/logout', [LoginController::class,'logout'])->name('logout');

    // SuperAdmin
    Route::middleware('role:SuperAdmin')->group(function () {

        Route::get('/superadmin/dashboard', [DashboardController::class, 'superadmin'])->name('superadmin.dashboard');
        Route::get('/superadmin/clients', [DashboardController::class, 'clients'])->name('superadmin.clients');
        Route::post('/superadmin/invite-client', [InvitationController::class, 'inviteClient'])->name('superadmin.invite-client');

    });


    // Admin
    Route::middleware('role:Admin')->group(function () {

        Route::get('/admin/dashboard', [ DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::post('/admin/invite-member', [InvitationController::class, 'inviteTeamMember'])->name('admin.invite-member');

    });


    // Member
    Route::middleware('role:Member')->group(function () {

        Route::get('/member/dashboard', [DashboardController::class,'member'])->name('member.dashboard');

    });


    // Admin and Member can create short urls (SuperAdmin cannot)
    Route::middleware('role:Admin,Member')->group(function () {

        Route::post('/short-urls', [ShortUrlController::class, 'store'])->name('short-urls.store');

    });


    // Full short url list (View All) for SuperAdmin and Admin
    Route::get('/short-urls', [ShortUrlController::class, 'index'])->middleware('role:SuperAdmin,Admin')->name('short-urls.index');


    // Everyone can download the list they are allowed to see
    Route::get('/short-urls/download', [ShortUrlController::class, 'download'])->middleware('role:SuperAdmin,Admin,Member')->name('short-urls.download');

});

Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role?->name) {

        'SuperAdmin' => redirect()->route('superadmin.dashboard'),
        'Admin' => redirect()->route('admin.dashboard'),
        'Member' => redirect()->route('member.dashboard'),

        default => abort(403),

    };

});
