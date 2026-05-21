<?php

use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [SocialAuthController::class, 'callback']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('/download/xml/{access_key}', [DownloadController::class, 'xml']);
    Route::get('/download/pdf/{access_key}', [DownloadController::class, 'pdf']);
    Route::get('/download/result/{token}', [DownloadController::class, 'result']);
});

require __DIR__.'/settings.php';
