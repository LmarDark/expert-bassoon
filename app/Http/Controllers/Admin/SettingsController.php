<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

final class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings', [
            'currentSettings' => Setting::loginSettings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_app_name' => ['nullable', 'string', 'max:255'],
            'login_show_logo' => ['required', 'boolean'],
            'login_primary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'login_custom_css' => ['nullable', 'string'],
            'login_bg_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'login_logo' => ['nullable', 'file', 'image', 'max:2048'],
            'login_logo_remove' => ['nullable', 'boolean'],
        ]);

        Setting::set('login_app_name', $validated['login_app_name'] ?? null);
        Setting::set('login_show_logo', $validated['login_show_logo'] ? '1' : '0');
        Setting::set('login_primary_color', $validated['login_primary_color'] ?? null);
        Setting::set('login_custom_css', $validated['login_custom_css'] ?? null);
        Setting::set('login_bg_color', $validated['login_bg_color'] ?? null);

        if (! empty($validated['login_logo_remove'])) {
            $oldPath = Setting::get('login_logo_path');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            Setting::set('login_logo_path', null);
        } elseif ($request->hasFile('login_logo')) {
            $oldPath = Setting::get('login_logo_path');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('login_logo')->store('logos', 'public');
            Setting::set('login_logo_path', is_string($path) ? $path : null);
        }

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Configurações salvas com sucesso.');
    }
}
