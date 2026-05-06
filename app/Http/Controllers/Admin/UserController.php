<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Auth\UsernameValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()->orderBy('created_at', 'desc')
                ->get(['id', 'username', 'nickname', 'is_admin', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'usernameValidationType' => Setting::usernameValidation()['type'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validation = Setting::usernameValidation();
        $usernameRules = [
            'required', 'string', 'max:255', 'unique:users,username',
            ...UsernameValidationService::buildRules($validation['type'], $validation['custom_pattern']),
        ];

        $validated = $request->validate([
            'nickname' => ['nullable', 'string', 'max:255'],
            'username' => $usernameRules,
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->create([
            'nickname' => ($validated['nickname'] ?? '') ?: null,
            'username' => $validated['username'],
            'password' => $validated['password'],
            'is_admin' => (bool) ($validated['is_admin'] ?? false),
        ]);

        ActivityLog::create([
            'actor_id' => Auth::id(),
            'event' => 'user_created',
            'target_username' => $user->username,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->only('id', 'username', 'nickname', 'is_admin', 'created_at'),
            'usernameValidationType' => Setting::usernameValidation()['type'],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validation = Setting::usernameValidation();
        $usernameRules = [
            'required', 'string', 'max:255', 'unique:users,username,'.$user->id,
            ...UsernameValidationService::buildRules($validation['type'], $validation['custom_pattern']),
        ];

        $validated = $request->validate([
            'nickname' => ['nullable', 'string', 'max:255'],
            'username' => $usernameRules,
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['required', 'boolean'],
        ]);

        if ($user->id === Auth::id() && ! $validated['is_admin']) {
            return back()->withErrors(['is_admin' => 'Você não pode remover seu próprio privilégio de administrador.']);
        }

        $user->nickname = ($validated['nickname'] ?? '') ?: null;
        $user->username = $validated['username'];
        $user->is_admin = $validated['is_admin'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        ActivityLog::create([
            'actor_id' => Auth::id(),
            'event' => 'user_updated',
            'target_username' => $user->username,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            abort(403);
        }

        $username = $user->username;
        $user->delete();

        ActivityLog::create([
            'actor_id' => Auth::id(),
            'event' => 'user_deleted',
            'target_username' => $username,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso.');
    }
}
