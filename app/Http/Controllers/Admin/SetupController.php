<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class SetupController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Admin/Setup');
    }

    public function store(Request $request): RedirectResponse
    {
        $validationType = (string) $request->input('validation_type', '');
        $customPattern = (string) $request->input('custom_pattern', '');

        $usernameRules = ['required', 'string', 'max:255', 'unique:users,username'];

        if ($validationType === 'cpf') {
            $usernameRules[] = static function (string $attribute, mixed $value, Closure $fail): void {
                $cpf = preg_replace('/[^0-9]/', '', (string) $value) ?? '';

                if (mb_strlen($cpf) !== 11 || (bool) preg_match('/^(\d)\1{10}$/', $cpf)) {
                    $fail('CPF inválido.');

                    return;
                }

                for ($t = 9; $t < 11; $t++) {
                    $sum = 0;

                    for ($i = 0; $i < $t; $i++) {
                        $sum += (int) $cpf[$i] * ($t + 1 - $i);
                    }

                    $digit = (10 * $sum % 11) % 10;

                    if ((int) $cpf[$t] !== $digit) {
                        $fail('CPF inválido.');

                        return;
                    }
                }
            };
        } elseif ($validationType === 'celular') {
            $usernameRules[] = static function (string $attribute, mixed $value, Closure $fail): void {
                if (! preg_match('/^\(?\d{2}\)?\s?9\d{4}[\s-]?\d{4}$/', (string) $value)) {
                    $fail('Número de celular inválido. Ex: (00) 90000-0000');
                }
            };
        } elseif ($validationType === 'personalizado' && $customPattern !== '') {
            $usernameRules[] = static function (string $attribute, mixed $value, Closure $fail) use ($customPattern): void {
                if (@preg_match('~'.$customPattern.'~', (string) $value) !== 1) {
                    $fail('O usuário não corresponde ao padrão personalizado.');
                }
            };
        }

        $validated = $request->validate([
            'nickname' => ['nullable', 'string', 'max:255'],
            'validation_type' => ['required', 'in:cpf,celular,personalizado'],
            'custom_pattern' => [
                Rule::requiredIf($validationType === 'personalizado'),
                'nullable',
                'string',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (@preg_match('~'.$value.'~', '') === false) {
                        $fail('Expressão regular inválida.');
                    }
                },
            ],
            'username' => $usernameRules,
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            if (User::query()->exists()) {
                abort(403);
            }

            return User::query()->create([
                'nickname' => $validated['nickname'] ?? null,
                'username' => $validated['username'],
                'password' => $validated['password'],
                'is_admin' => true,
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
