<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

if (!defined('LOGIN_PATH')) {
    define('LOGIN_PATH', '/login');
}

Route::redirect('/', LOGIN_PATH);

// Configuração inicial (sem usuários no banco)
Route::get('/setup', [SetupController::class, 'show'])->name('setup');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

// Autenticação
Route::post(LOGIN_PATH, [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Validação de sessão para o auth_request do Nginx (SSO)
Route::get('/auth/check', fn () => auth()->check()
    ? response()->noContent()
    : response()->noContent(401)
)->name('auth.check');

Route::middleware(['universal.auth'])->group(function () {
    Route::inertia(LOGIN_PATH, 'Auth/Login')->name('login');
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

    // Admin - gerenciamento de usuários (apenas admins)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
