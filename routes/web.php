<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomCategoryController;
use App\Http\Controllers\AmenitiesController;
use App\Http\Controllers\RoomDefinitionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HotelServiceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;
use App\Services\DashboardAnalyticsService;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\BackupController;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\RoomController as ClientRoomController;
use App\Http\Controllers\Client\BookingController as ClientBookingController;
use App\Http\Controllers\Client\RoomReviewController;
use App\Http\Controllers\Client\ServiceController as ClientServiceController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\ContactRequestController;

use App\Models\RoomDefinition;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/rooms', [ClientRoomController::class, 'index'])->name('client.rooms.index');

Route::get('/services', [ClientServiceController::class, 'index'])->name('client.services.index');

Route::get('/contact', [ContactController::class, 'index'])->name('client.contact.index');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:30,1')->name('client.contact.store');

Route::get('/rooms/{room}', [ClientRoomController::class, 'show'])->name('client.rooms.show');

Route::get('/booking/create', [ClientBookingController::class, 'create'])->middleware('auth')->name('client.booking.create');
Route::post('/booking/request', [ClientBookingController::class, 'store'])->middleware('auth')->name('client.booking.store');

Route::get('/my-bookings/{booking}/invoice', [ClientBookingController::class, 'invoice'])
    ->middleware('signed')
    ->name('client.bookings.invoice');

Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('/my-bookings')->name('client.bookings.')->group(function () {
        Route::get('/', [ClientBookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [ClientBookingController::class, 'show'])->name('show');
        Route::post('/{booking}/cancel', [ClientBookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking}/review', [RoomReviewController::class, 'store'])->name('review');
    });
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    Route::get('/dashboard', function (DashboardAnalyticsService $dashboardAnalyticsService) {
        return Inertia::render('Dashboard', [
            'dashboard' => $dashboardAnalyticsService->build(),
        ]);
    })->name('dashboard');

    Route::prefix('/manage')->group(function () {

        Route::resource('room-categories', RoomCategoryController::class)->names('admin.room-categories');
        Route::resource('rooms', RoomController::class)->names('admin.rooms');
        Route::resource('room-types', RoomTypeController::class)->names('admin.room-types');
        Route::resource('amenities', AmenitiesController::class)->names('admin.amenities');
        Route::resource('room-definitions', RoomDefinitionsController::class)->names('admin.room-definitions');

        Route::resource('users', UserController::class)->names('admin.users');
        Route::get('customers/{customer}/history', [CustomerController::class, 'history'])->name('admin.customers.history');
        Route::resource('customers', CustomerController::class)->names('admin.customers');

        Route::resource('services', HotelServiceController::class)->names('admin.services');

        Route::get('contact-requests', [ContactRequestController::class, 'index'])->name('admin.contact-requests.index');
        Route::patch('contact-requests/{contactRequest}/status', [ContactRequestController::class, 'updateStatus'])->name('admin.contact-requests.update-status');

        Route::post('api/available-rooms', [BookingController::class, 'getAvailableRoomsApi'])->name('admin.bookings.api-rooms');

        Route::post('bookings/quick-customer', [BookingController::class, 'quickStoreCustomer'])->name('admin.bookings.quick-customer');
        Route::get('bookings/search-customers', [BookingController::class, 'searchCustomers'])->name('admin.bookings.search-customers');

        Route::post('bookings/{booking}/add-service', [BookingController::class, 'addService'])->name('admin.bookings.add-service');
        Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('admin.bookings.update-status');
        Route::patch('rooms/{room}/status', [BookingController::class, 'updateRoomStatus'])->name('admin.rooms.update-status');

        Route::post('bookings/{booking}/deposit', [BookingController::class, 'deposit'])->name('admin.bookings.deposit');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('admin.bookings.cancel');
        Route::post('bookings/{booking}/transfer-room', [BookingController::class, 'transferRoom'])->name('admin.bookings.transfer-room');


        Route::post('bookings/{booking}/pay', [BookingController::class, 'payInvoice'])->name('admin.bookings.pay');
        Route::post('bookings/{booking}/check-in', [BookingController::class, 'processCheckIn'])->name('admin.bookings.check-in');
        Route::post('bookings/{booking}/check-out', [BookingController::class, 'processCheckOut'])->name('admin.bookings.check-out');

        Route::get('bookings-history', [BookingController::class, 'history'])->name('admin.bookings.history');

        Route::resource('bookings', BookingController::class)->names('admin.bookings');

        Route::get('invoices/export', [InvoiceController::class, 'export'])->name('admin.invoices.export');
        Route::resource('invoices', InvoiceController::class)->only(['index', 'show'])->names('admin.invoices');
        Route::post('bookings/{booking}/invoice', [InvoiceController::class, 'generate'])->name('admin.invoices.generate');
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('admin.invoices.pay');

        Route::resource('staff', StaffController::class)->names('admin.staffs');
        Route::resource('roles', RoleController::class)->names('admin.roles');

        Route::get('/backups', [BackupController::class, 'index'])->name('admin.backups.index');
        Route::post('/backups/create', [BackupController::class, 'create'])->name('admin.backups.create');
        Route::get('/backups/download', [BackupController::class, 'download'])->name('admin.backups.download');
        Route::delete('/backups/destroy', [BackupController::class, 'destroy'])->name('admin.backups.destroy');
    });
});

require __DIR__.'/auth.php';
