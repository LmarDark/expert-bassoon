<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sso\TokenController;
use Illuminate\Support\Facades\Route;

if (! defined('LOGIN_PATH')) {
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

// Health check
Route::get('/health', HealthController::class)->name('health');

// SSO JWT (cross-domain) — validate e logout são públicos; token requer sessão ativa
Route::post('/sso/validate', [TokenController::class, 'validate'])->name('sso.validate');
Route::get('/sso/logout', [TokenController::class, 'logout'])->name('sso.logout');

Route::middleware(['universal.auth'])->group(function () {
    Route::inertia(LOGIN_PATH, 'Auth/Login')->name('login');
    Route::inertia('/home', 'Home')->name('home');

    // Perfil do usuário autenticado
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // SSO — emissão de token (requer sessão ativa)
    Route::get('/sso/token', [TokenController::class, 'issue'])->name('sso.token');

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // Auditoria
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

        // Configurações da página de login
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // Gerenciamento de usuários
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Gerenciamento de aplicações SSO
        Route::get('/apps', [AppController::class, 'index'])->name('apps.index');
        Route::get('/apps/create', [AppController::class, 'create'])->name('apps.create');
        Route::post('/apps', [AppController::class, 'store'])->name('apps.store');
        Route::get('/apps/{app}/edit', [AppController::class, 'edit'])->name('apps.edit');
        Route::put('/apps/{app}', [AppController::class, 'update'])->name('apps.update');
        Route::delete('/apps/{app}', [AppController::class, 'destroy'])->name('apps.destroy');
        Route::post('/apps/{app}/regenerate-key', [AppController::class, 'regenerateApiKey'])->name('apps.regenerate-key');
    });
});
