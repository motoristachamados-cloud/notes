<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/google', [AuthController::class, 'google']);
Route::post('/payments/webhook', [PaymentsController::class, 'webhook'])->middleware('throttle:30,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', MeController::class);
    Route::get('/wallet', WalletController::class);
    Route::post('/payments/create', [PaymentsController::class, 'create']);
});
