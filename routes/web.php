<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
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
    // Nhóm quản lý tài khoản mặc định
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Nhóm tính năng Quản lý khách sạn
    Route::get('/manage/rooms/matrix', [RoomController::class, 'matrix'])->name('manage.rooms.matrix');
    Route::post('/manage/rooms/{id}/status', [RoomController::class, 'updateStatus'])->name('manage.rooms.updateStatus');

    // routes/web.php

    Route::get('/manage/room-types/create', [RoomTypeController::class, 'create'])->name('admin.room-types.create');
    Route::post('/manage/room-types', [RoomTypeController::class, 'store'])->name('admin.room-types.store');

});

require __DIR__.'/auth.php';