<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AppController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Apps/Index', [
            'apps' => App::query()->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'api_key', 'allowed_domains', 'callback_url', 'active', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Apps/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'allowed_domains' => ['required', 'string'],
            'callback_url' => ['nullable', 'url', 'max:500'],
            'active' => ['nullable', 'boolean'],
        ]);

        App::query()->create([
            'name' => $validated['name'],
            'api_key' => App::generateApiKey(),
            'allowed_domains' => self::parseDomains($validated['allowed_domains']),
            'callback_url' => ($validated['callback_url'] ?? '') ?: null,
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return redirect()->route('admin.apps.index')
            ->with('success', 'Aplicação criada com sucesso.');
    }

    public function edit(App $app): Response
    {
        return Inertia::render('Admin/Apps/Edit', [
            'app' => $app->only('id', 'name', 'api_key', 'allowed_domains', 'callback_url', 'active'),
        ]);
    }

    public function update(Request $request, App $app): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'allowed_domains' => ['required', 'string'],
            'callback_url' => ['nullable', 'url', 'max:500'],
            'active' => ['required', 'boolean'],
        ]);

        $app->name = $validated['name'];
        $app->allowed_domains = self::parseDomains($validated['allowed_domains']);
        $app->callback_url = ($validated['callback_url'] ?? '') ?: null;
        $app->active = $validated['active'];
        $app->save();

        return redirect()->route('admin.apps.index')
            ->with('success', 'Aplicação atualizada com sucesso.');
    }

    public function destroy(App $app): RedirectResponse
    {
        $app->delete();

        return redirect()->route('admin.apps.index')
            ->with('success', 'Aplicação removida com sucesso.');
    }

    public function regenerateApiKey(App $app): RedirectResponse
    {
        $app->api_key = App::generateApiKey();
        $app->save();

        return redirect()->route('admin.apps.edit', $app)
            ->with('success', 'API Key regenerada com sucesso.');
    }

    /** @return list<string> */
    private static function parseDomains(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", str_replace(["\r\n", "\r", ','], "\n", $raw))),
            fn (string $d) => $d !== ''
        ));
    }
}
