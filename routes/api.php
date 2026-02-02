<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppBotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('bot')->group(function () {
    Route::get('/busy-slots', [WhatsAppBotController::class, 'getBusySlots']);
    Route::post('/status', [WhatsAppBotController::class, 'updateStatus']);
    Route::post('/reschedule', [WhatsAppBotController::class, 'reschedule']);
    Route::get('/get-link', [WhatsAppBotController::class, 'getRescheduleLink']);
});
