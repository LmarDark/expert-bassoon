<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\UniversalAuth;

if (!defined('LOGIN_PATH')) {
    define('LOGIN_PATH', '/login');
}

Route::redirect('/', LOGIN_PATH);

Route::post(LOGIN_PATH, [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['universal.auth'])->group(function () {
    Route::inertia(LOGIN_PATH, 'Auth/Login')->name('login');
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
});
