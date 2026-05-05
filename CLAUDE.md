# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Centralized SSO authentication gateway built with **Laravel 13**, **Inertia.js**, and **Vue 3**. Multiple applications share a single authenticated session via a shared-domain cookie. Nginx delegates session validation to the `/auth/check` endpoint via the `auth_request` module.

## Commands

### Development

```bash
composer run dev       # PHP server + queue + logs + Vite HMR (all in parallel)
composer run setup     # Full first-time install (deps, .env, key, migrate, pnpm build)
```

### Testing

```bash
php artisan test                        # Run all tests
php artisan test --filter ClassName     # Run a single test class
php artisan test --filter method_name   # Run a single test method
composer run test                       # config:clear + lint:check + tests
```

### Linting & Static Analysis

```bash
composer run lint          # Fix PHP style with Pint (parallel)
composer run lint:check    # Check PHP style without modifying
composer run analyse       # PHPStan level 6 via Larastan
pnpm run lint              # Fix ESLint issues
pnpm run lint:check        # Check ESLint without modifying
pnpm run format            # Format with Prettier
pnpm run format:check      # Check formatting without modifying
pnpm run types:check       # TypeScript type check (vue-tsc --noEmit)
composer run ci:check      # Full CI: pnpm lint + format + types + PHP test suite
```

### Build

```bash
pnpm run build    # Production build
pnpm run dev      # Vite dev server with HMR
```

## Architecture

### SSO Flow

```
Browser → protected-app.domain.com
    │
    ├─ Nginx auth_request → auth.domain.com/auth/check
    │       ├─ 200: serve the request normally
    │       └─ 401: redirect to auth.domain.com/login?return_to=...
    │
    └─ User logs in → redirected back to original app
```

The `return_to` host is validated against `ALLOWED_HOST_REDIRECT` (env var → `config('app.allowed_host_redirect')`) to prevent open redirect attacks.

### Middleware Stack (applied globally via `bootstrap/app.php`)

- **`CheckFirstSetup`** — runs before auth; redirects to `/setup` when no users exist, redirects away from `/setup` when users do exist.
- **`UniversalAuth`** — applied to all named routes; redirects unauthenticated users to `/login?return_to=...`, and authenticated users away from `/login` to `/home`.
- **`EnsureIsAdmin`** (`admin` alias) — gates all `/admin/*` routes; aborts 403 if `user->is_admin` is false.

### Backend Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/AuditController.php     # Activity log listing (admin only)
│   │   ├── Admin/SetupController.php     # First-run admin setup
│   │   ├── Admin/SettingsController.php  # Login page customization (admin only)
│   │   ├── Admin/UserController.php      # User CRUD (admin only)
│   │   ├── Auth/AuthController.php       # Login / logout
│   │   └── ProfileController.php         # Authenticated user's own profile
│   ├── Middleware/
│   │   ├── CheckFirstSetup.php
│   │   ├── UniversalAuth.php
│   │   └── EnsureIsAdmin.php
│   └── Requests/Auth/LoginRequest.php
├── Models/
│   ├── ActivityLog.php  # Immutable event log (actor_id, event, target_username, ip_address)
│   ├── Setting.php      # Key-value settings store; use Setting::loginSettings() to read all login settings at once
│   └── User.php         # username (unique), nickname, password (hashed), is_admin
└── Services/Auth/AuthService.php        # Wraps Auth::attempt()
```

#### Activity log events

| Event | Logged by |
|-------|-----------|
| `login_success` | AuthController |
| `login_failed` | AuthController |
| `logout` | AuthController |
| `user_created` | UserController |
| `user_updated` | UserController |
| `user_deleted` | UserController |

#### Login page settings (stored in `settings` table)

| Key | Default | Description |
|-----|---------|-------------|
| `login_app_name` | `Sistema de Autenticação` | Title shown on login page |
| `login_show_logo` | `1` | Toggle logo visibility (`1`/`0`) |
| `login_primary_color` | `#F53003` | Logo color (hex) |
| `login_custom_css` | `""` | CSS injected only on the login page |

Settings are shared globally via `HandleInertiaRequests` under `settings.login`. The `Login.vue` reads them via `usePage()`. Custom CSS is injected programmatically via `onMounted` (not via `<style>` tag) to avoid HTML encoding issues.

Authentication uses `username` (not email). The `User` model uses PHP 8 attribute-based `#[Fillable]` and `#[Hidden]` instead of array properties.

### Frontend Structure

The frontend is **Inertia.js + Vue 3 + TypeScript + Tailwind CSS 4**. There is no client-side router — page components in `resources/js/pages/` map directly to Inertia responses from controllers.

**Wayfinder** (`@laravel/vite-plugin-wayfinder`) auto-generates type-safe route helpers into `resources/js/actions/` and `resources/js/routes/` at build/dev time. These files are generated — do not edit them manually. Import routes from `resources/js/routes/` and actions from `resources/js/actions/`.

```
resources/js/
├── app.ts                    # Inertia app bootstrap
├── components/               # Reusable Vue components
│   ├── AppLogo.vue           # Brand SVG logo (use class="h-6" or "h-8" to set size)
│   ├── AppHeader.vue         # Authenticated header: logo + username + logout
│   ├── PasswordInput.vue     # Password field with show/hide toggle (v-model compatible)
│   └── ActionCard.vue        # Linked card with icon slot, title, description
├── pages/                    # One Vue component per route
│   ├── Auth/Login.vue
│   ├── Admin/Audit.vue
│   ├── Admin/Settings.vue
│   ├── Admin/Setup.vue
│   ├── Admin/Users/{Index,Create,Edit}.vue
│   ├── Home.vue
│   └── Profile/Edit.vue
├── actions/                  # Generated by Wayfinder — form action helpers
├── routes/                   # Generated by Wayfinder — route URL helpers
├── types/
│   ├── auth.ts               # User / Auth shared types
│   └── global.d.ts
└── wayfinder/index.ts        # Wayfinder runtime (queryParams, setUrlDefaults, etc.)
```

#### Component API reference

| Component | Key props | Notes |
|-----------|-----------|-------|
| `AppLogo` | — | Pass `class="h-6"` or `class="h-8"` for size |
| `AppHeader` | `user: { username, nickname? }` | Handles logout internally via `router.post('/logout')` |
| `PasswordInput` | `id`, `label`, `modelValue`, `error?`, `required?`, `autocomplete?` | Emits `update:modelValue`; use `:model-value` + `@update:model-value` with Inertia `useForm` |
| `ActionCard` | `href`, `title`, `description` | Named slot `#icon` for the SVG icon |

### Key Routes

| Method | URI | Auth |
|--------|-----|------|
| `GET` | `/auth/check` | — (Nginx SSO probe) |
| `GET/POST` | `/login` | — |
| `GET/POST` | `/setup` | — (only when no users exist) |
| `GET` | `/home` | Required |
| `GET/PUT` | `/profile` | Required |
| `GET` | `/admin/audit` | Required + is_admin |
| `GET/PUT` | `/admin/settings` | Required + is_admin |
| `GET/POST/PUT/DELETE` | `/admin/users*` | Required + is_admin |

### PHP Code Conventions (enforced by Pint)

- `declare(strict_types=1)` at the top of every PHP file
- All classes are `final` by default
- No `else` when avoidable (`no_useless_else`, `no_superfluous_elseif`)
- Fully qualified imports — no leading `\` on global namespaces
- Multibyte string functions (`mb_str_functions`)

### Critical Environment Variables for SSO

| Variable | Production value |
|----------|-----------------|
| `SESSION_DOMAIN` | `.yourdomain.com` (leading dot shares across subdomains) |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_SAME_SITE` | `lax` |
| `ALLOWED_HOST_REDIRECT` | `yourdomain.com` |

### Shared Inertia props

`HandleInertiaRequests::share()` injects on every response:

| Key | Type | Description |
|-----|------|-------------|
| `auth.user` | `{ username, nickname?, is_admin }` or `null` | Authenticated user |
| `flash.success` | `string` or `null` | One-shot success message from `->with('success', ...)` |
| `settings.login` | `{ app_name, show_logo, primary_color, custom_css }` | Login page settings (falls back to defaults via `rescue()` if table missing) |

### Database

SQLite by default (`database/database.sqlite`). Session driver is `database`.

| Table | Purpose |
|-------|---------|
| `users` | Auth; `username` is the unique login key (not email) |
| `sessions` | Laravel session store |
| `activity_logs` | Immutable event log; `UPDATED_AT = null`; actor nullable for failed logins |
| `settings` | Key-value store; primary key is `key` (string); no timestamps |

> **Vite manifest and tests**: `public/build` is gitignored. Tests that render Inertia pages (e.g. `assertOk()` on `/home`) require a built manifest. Run `pnpm run build` after adding new page components, otherwise those tests will fail with *"Unable to locate file in Vite manifest"*.
