<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomCategoryController;
use App\Http\Controllers\AmenitiesController;
use App\Http\Controllers\RoomDefinitionsController;
use App\Services\RoomService;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. DÀNH CHO KHÁCH HÀNG (Không cần đăng nhập)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/rooms', [RoomTypeController::class, 'index'])->name('client.rooms.index');
Route::get('/rooms/{slug}', [RoomTypeController::class, 'show'])->name('client.rooms.show');

// 2. DÀNH CHO LỄ TÂN / QUẢN TRỊ (Bắt buộc đăng nhập)
Route::get('/dashboard', function (RoomService $roomService) {
    return Inertia::render('Dashboard', [
        'stats' => $roomService->getDashboardStats()
    ]); // Dùng lại file mặc định của Breeze
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    //dashboard chính
    route::get('/dashboard', function (RoomService $roomService) {
        return Inertia::render('Dashboard', [
            'stats' => $roomService->getDashboardStats()
        ]);
    })->name('dashboard');

    // Nhóm quản lý tài khoản mặc định
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    route::prefix('/manage')->group(function () {
        // Nhóm tính năng Quản lý hạng phòng
        route::resource('room-categories', RoomCategoryController::class)->names('admin.room-categories');
        route::resource('rooms', RoomController::class)->names('admin.rooms');

        route::resource('room-types', RoomTypeController::class)->names('admin.room-types');
        route::resource('amenities', AmenitiesController::class)->names('admin.amenities');
        route::resource('room-definitions', RoomDefinitionsController::class)->names('admin.room-definitions');
    });
});

require __DIR__.'/auth.php';