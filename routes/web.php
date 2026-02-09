<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ServiceController;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CategoryController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/agendar', [PublicAppointmentController::class, 'create'])->name('appointments.create');
Route::post('/agendar', [PublicAppointmentController::class, 'store'])->name('appointments.store');
Route::get('/reprogramar/{token}', [PublicAppointmentController::class, 'reschedule'])->name('public.appointments.reschedule');
Route::post('/reprogramar/{token}', [PublicAppointmentController::class, 'updateReschedule'])->name('public.appointments.updateReschedule');


// Auth Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Protected)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::delete('services/images/{image}', [ServiceController::class, 'destroyImage'])->name('services.destroyImage');
    Route::delete('services/{service}/image', [ServiceController::class, 'destroyMainImage'])->name('services.destroyMainImage');
    Route::resource('services', ServiceController::class);
    Route::get('appointments/check-availability', [AppointmentController::class, 'checkAvailability'])->name('appointments.checkAvailability');
    Route::post('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::resource('appointments', AppointmentController::class);
    Route::resource('notifications', NotificationController::class);
    
    // Availability
    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::get('availability/get', [AvailabilityController::class, 'show'])->name('availability.show');
    Route::post('availability', [AvailabilityController::class, 'store'])->name('availability.store');
    Route::delete('availability', [AvailabilityController::class, 'destroy'])->name('availability.destroy');
});

