<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\UniversalAuth;

Route::redirect('/', '/login');

Route::inertia('/login', 'Auth/Login')->name('login')->middleware('universal.auth');
Route::inertia('/dashboard', 'Dashboard')->name('dashboard')->middleware('universal.auth');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

