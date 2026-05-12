---
description: Vormia UI Livewire Flux Admin - developer guide (structure, UI, AI prompts, Fortify, roles)
globs: "**/*.blade.php,**/*.php"
alwaysApply: false
---

# Vormia UI Livewire Flux Admin — Developer Guide

Single reference for **package layout**, **admin UI flow and style**, **AI prompt workflow**, **Laravel Fortify** (password stubs and active users), and **assigning roles on registration**. The YAML frontmatter above is the same scope as the former `docs/ui-rules.md` (Cursor-style hints for Blade and PHP); add this file to Project Rules if you want it applied automatically.

## Table of contents

1. [Quick reference (repo layout and patterns)](#quick-reference-repo-layout-and-patterns)
2. [UI: flow and style](#ui-flow-and-style)
3. [AI: promptbook and workflow](#ai-promptbook-and-workflow)
4. [Fortify: passwords, publish, and active users](#fortify-passwords-publish-and-active-users)
5. [Roles: assign on registration](#roles-assign-on-registration)

---

## Quick reference (repo layout and patterns)

### Project structure (stubs → app after install)

- Admin Livewire views: `resources/views/livewire/admin/`
- Control sections (categories, inheritance, locations, availability): `livewire/admin/control/<module>/`
- Admins: `livewire/admin/admins/`
- Use **Livewire 4 anonymous single-file components** in `.blade.php` (`new class extends Component`), default Livewire layout — **no** `#[Layout(...)]`. Not Volt-specific.

### Sidebar

- Primary: `resources/views/layouts/app/sidebar.blade.php`
- Fallback: `resources/views/components/layouts/app/sidebar.blade.php`
- Inject menu **inside** the Platform `flux:sidebar.group`, before `</flux:sidebar.group>`
- Use `flux:sidebar.item` with `wire:navigate` where the stubs do
- Reference: `src/stubs/reference/sidebar-menu-to-add.blade.php`

### AdminPanel

- Blade: `resources/views/components/admin-panel.blade.php`
- Class: `app/View/Components/AdminPanel.php`
- Slots: `header`, `desc`, `button`, default slot
- Wrap admin pages in `<x-admin-panel>`; use `WithNotifications` and `{!! $this->renderNotification() !!}`
- Match stub **light + dark** Tailwind (`dark:*` classes) — see [UI: flow and style](#ui-flow-and-style)

### Patterns

- `Vormia\Vormia\Traits\Livewire\WithNotifications`; pagination / `#[Validate]` / `#[Computed]` as in stubs
- Route names: `admin.<section>.index`, `.create`, `.edit`
- Password fields in admin-user flows: `App\Actions\Fortify\PasswordValidationRules` after Fortify publish — see [Fortify: passwords, publish, and active users](#fortify-passwords-publish-and-active-users)

---

## UI: flow and style

This section is for **developers consuming** `vormiaphp/ui-livewireflux-admin` in their Laravel app and building new admin pages that match the package’s **flow and visual style**.

### What “matches the package UI”

When you add a new admin module, it should follow the same shape as the existing stubs in:

- `src/stubs/resources/views/livewire/admin/**`

The consistent “look & flow” comes from:

- **Layout**: admin pages rely on your app’s **default Livewire layout** (no `#[Layout(...)]` required)
- **Shell**: pages render inside `<x-admin-panel>` with `header`, `desc`, and an optional “action” button slot
- **Patterns**:
  - `index` pages: search, pagination, table list, row actions (edit / activate / deactivate / delete)
  - `create` and `edit` pages: form + validation + notification feedback + “Go back” button
- **Feedback**: pages use the `WithNotifications` trait and render notifications via `{!! $this->renderNotification() !!}`
- **Navigation**: sidebar uses `flux:sidebar.item` entries (see `src/stubs/reference/sidebar-menu-to-add.blade.php`)

### Your mental model (flow)

Most modules follow this exact 3-page flow:

1. **Index**: list records, search, paginate, quick actions
2. **Create**: create a new record, then redirect back or reset the form
3. **Edit**: edit an existing record

Routes are Livewire routes (see `src/stubs/reference/routes-to-add.php`) with names like:

- `admin.<module>.index`
- `admin.<module>.create`
- `admin.<module>.edit`

### Recommended folder structure (when adding a new module)

In your **app** (after install/copy), keep the same structure:

```
resources/views/livewire/admin/
  control/
    <module>/
      index.blade.php
      create.blade.php
      edit.blade.php
```

And add the 3 routes in `routes/web.php` under the `admin` prefix group.

### UI building blocks used by this package

#### 1) Admin layout

Every page begins with a PHP block that declares an anonymous Livewire component. It relies on your app’s default Livewire layout:

```php
<?php

use Livewire\Component;
use Vormia\Vormia\Traits\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;

    // ...
}; ?>
```

#### 2) The AdminPanel shell

Wrap your page content like:

```blade
<x-admin-panel>
    <x-slot name="header">{{ __('My Module') }}</x-slot>
    <x-slot name="desc">
        {{ __('Explain what the module manages.') }}
    </x-slot>
    <x-slot name="button">
        <a href="{{ route('admin.my-module.create') }}"
           class="bg-blue-500 dark:bg-blue-600 text-white hover:bg-blue-600 dark:hover:bg-blue-700 px-3 py-2 rounded-md float-right text-sm font-bold">
            Add New
        </a>
    </x-slot>

    {!! $this->renderNotification() !!}

    <!-- Your content -->
</x-admin-panel>
```

The component class stub lives in `src/stubs/app/View/Components/AdminPanel.php` (it renders `components.admin-panel` in the consuming app).

#### 3) Tailwind style conventions (as seen in stubs)

The stubs consistently use:

- **Cards**: `bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg`
- **Tables**:
  - header row: `bg-gray-50 dark:bg-gray-700`
  - zebra rows: `even:bg-gray-50 dark:even:bg-gray-800/50`
  - dividers: `divide-y divide-gray-300 dark:divide-gray-600`
- **Inputs**:
  - `rounded-md bg-white dark:bg-gray-700 ... outline-gray-300 dark:outline-gray-600 focus:outline-indigo-600`
- **Action buttons**:
  - edit: `bg-indigo-600 hover:bg-indigo-500`
  - activate: `bg-green-600 hover:bg-green-500`
  - deactivate: `bg-yellow-400 hover:bg-yellow-500`
  - delete: `bg-red-600 hover:bg-red-500`

### Copy/paste templates

#### Index page template (list/search/pagination/actions)

Use this as your base. It matches the patterns used by e.g. `control/categories/index.blade.php`.

```php
<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Vormia\Vormia\Traits\Livewire\WithNotifications;

new class extends Component {
    use WithPagination;
    use WithNotifications;

    public $search = '';
    public $perPage = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function results()
    {
        // Replace this query with your model query.
        $query = \Illuminate\Database\Eloquent\Model::query();

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function delete($id): void
    {
        try {
            $row = \Illuminate\Database\Eloquent\Model::find($id);
            if (! $row) {
                $this->notifyError(__('Record not found!'));
                return;
            }

            $row->delete();
            $this->notifySuccess(__('Deleted successfully!'));
        } catch (\Throwable $e) {
            $this->notifyError(__('Delete failed: ').$e->getMessage());
        }
    }
}; ?>
```

```blade
<div>
    <x-admin-panel>
        <x-slot name="header">{{ __('My Module') }}</x-slot>
        <x-slot name="desc">{{ __('Manage items for My Module.') }}</x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.my-module.create') }}"
               class="bg-blue-500 dark:bg-blue-600 text-white hover:bg-blue-600 dark:hover:bg-blue-700 px-3 py-2 rounded-md float-right text-sm font-bold">
                Add New
            </a>
        </x-slot>

        {{-- Search --}}
        <div class="my-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Search</h3>
                    <div class="w-full sm:max-w-xs">
                        <input type="text" wire:model.live.debounce.300ms="search"
                               class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                               placeholder="Search..." />
                    </div>
                </div>
            </div>
        </div>

        {!! $this->renderNotification() !!}

        {{-- List --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg mt-2">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-3">#</th>
                    <th class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-3">Name</th>
                    <th class="relative py-3.5 pr-4 pl-3 sm:pr-3"><span class="sr-only">Actions</span></th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800">
                @forelse ($this->results as $row)
                    <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                        <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100 sm:pl-3">{{ $row->id }}</td>
                        <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100 sm:pl-3">{{ $row->name ?? '-' }}</td>
                        <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                            <a href="{{ route('admin.my-module.edit', $row->id) }}"
                               class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500">
                                Edit
                            </a>
                            <button type="button" wire:click="delete({{ $row->id }})"
                                    class="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-red-500">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-8 text-center text-gray-500 dark:text-gray-400 font-bold">
                            No results found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="p-4">
                {{ $this->results->links() }}
            </div>
        </div>
    </x-admin-panel>
</div>
```

#### Create/Edit template (form + validation + “Go back”)

Use the same building blocks the stubs use:

- `#[Validate(...)]` for properties
- `$this->validate()` in `save()` / `update()`
- success/error notifications via `notifySuccess()` / `notifyError()`
- “Go back” button styled like other stubs

If the form includes **password** fields (for example admin user create/edit), follow `resources/views/livewire/admin/admins/create.blade.php` and `edit.blade.php`: use Fortify’s `PasswordValidationRules` trait from `App\Actions\Fortify\PasswordValidationRules` (requires Fortify stubs in `app/Actions/Fortify/` — see [Fortify: passwords, publish, and active users](#fortify-passwords-publish-and-active-users)).

### Sidebar + navigation (Flux)

To add your module to the sidebar, follow the same style as:

- `src/stubs/reference/sidebar-menu-to-add.blade.php`

Use `wire:navigate` so navigation stays snappy with Livewire.

### Consistency checklist (before you call it “done”)

- **Routes**: you have `index/create/edit` route names that match the existing conventions
- **Layout**: you are using your app’s default Livewire layout (no `#[Layout(...)]` attribute)
- **Shell**: pages use `<x-admin-panel>` with `header`, `desc`, and a top-right action button (where relevant)
- **Feedback**: `{!! $this->renderNotification() !!}` is present and `WithNotifications` is used
- **Dark mode**: you used `dark:*` classes similar to existing stubs
- **Tables**: `divide-*`, `even:*` zebra rows, and consistent action button colors

### Livewire/Flux docs (official references)

- Livewire docs: [https://livewire.laravel.com/docs](https://livewire.laravel.com/docs)
- Flux docs: [https://fluxui.dev/docs](https://fluxui.dev/docs)

---

## AI: promptbook and workflow

This section is a **copy/paste promptbook** plus a **repeatable workflow** for using an AI assistant to build new admin UI screens that match the **flow and style** of `vormiaphp/ui-livewireflux-admin`.

It aligns with [UI: flow and style](#ui-flow-and-style) above and the real stub patterns under:

- `src/stubs/resources/views/livewire/admin/**`

### What “consistent UI” means in this package

Your generated screens should match these conventions:

- **Livewire anonymous components** at the top of each Blade file
- **Layout**: rely on your app’s **default Livewire layout** (no `#[Layout(...)]` attribute)
- **Shell**: wrap content in `<x-admin-panel>` slots (`header`, `desc`, `button`)
- **Notifications**: `use WithNotifications;` and `{!! $this->renderNotification() !!}`
- **Index flow**: search + pagination + table + action buttons
- **Create/Edit flow**: form + validation + success/error notification + “Go back” link
- **Flux sidebar navigation** uses `flux:sidebar.item` with `wire:navigate`

### Workflow (recommended every time)

#### Step 0 — Decide the module contract

Before you ask the AI to code, define these details (1 minute):

- **Module name**: e.g. `amenities`, `property-types`, `currencies`
- **Routes**:
  - `admin.<module>.index`
  - `admin.<module>.create`
  - `admin.<module>.edit`
- **Data model**:
  - which model will be listed/created/edited?
  - what are the fields?
  - any relationships for display?
- **Access rules**:
  - is it `auth` only, or role restricted?

#### Step 1 — Give the AI the “style anchors”

Always provide:

- One existing **index** stub (example: `src/stubs/resources/views/livewire/admin/control/categories/index.blade.php`)
- One existing **create/edit** stub (example: the matching `create.blade.php` / `edit.blade.php`)
- The requirement list from [What “matches the package UI”](#what-matches-the-package-ui) in this guide

This keeps the AI from inventing a different layout or CSS style.

#### Step 2 — Generate pages in this order

Ask the AI to build in this order:

1. `index.blade.php`
2. `create.blade.php`
3. `edit.blade.php`
4. Routes snippet to add to `routes/web.php`
5. Sidebar entry snippet (optional)

#### Step 3 — Review checklist (don’t skip)

Before you accept the AI output, check:

- **Layout**: pages rely on your app’s default Livewire layout (no `#[Layout(...)]`)
- **Shell**: every page wraps content in `<x-admin-panel>`
- **Notifications**: `{!! $this->renderNotification() !!}` exists and trait is used
- **Dark mode**: uses `dark:*` classes like the stubs
- **Action buttons**: follow the same color semantics (indigo/edit, green/activate, yellow/deactivate, red/delete)
- **Model namespaces**: use package models when required (example from current stubs: `use Vormia\Vormia\Models\Taxonomy;`)
- **App-level classes when matching existing stubs**: admin user screens intentionally use `App\Models\User` and `App\Actions\Fortify\PasswordValidationRules` (Fortify must be published into `app/Actions/Fortify/` — see [Fortify: passwords, publish, and active users](#fortify-passwords-publish-and-active-users)). For brand-new modules under `control/<module>/`, prefer Vormia package models unless the feature truly belongs in `App\...`.

### Promptbook (copy/paste)

#### Prompt 1 — Create a new admin module (index/create/edit)

Paste this prompt and fill the placeholders.

```text
You are helping me extend a Laravel admin UI built with Livewire 4 + Flux.

Goal: Create a new admin module named: <module_slug>

It MUST match these conventions:
- Use Livewire anonymous components at the top of each Blade file.
- Use the app’s default Livewire layout (do NOT add `#[Layout(...)]`).
- Wrap all content with <x-admin-panel> and provide header/desc/button slots.
- Use the WithNotifications trait and render notifications using: {!! $this->renderNotification() !!}.
- Follow the same Tailwind classes and table/list patterns used in these reference stubs:
  - <paste path to an existing index stub>
  - <paste path to an existing create stub>
  - <paste path to an existing edit stub>

Data model:
- Model class: <FQCN_or_import_name>
- Fields:
  - <field_1> (validation: <rules>)
  - <field_2> (validation: <rules>)
  - ...
- List columns for index: <columns...>
- Search fields: <fields...>

Output:
1) resources/views/livewire/admin/control/<module_slug>/index.blade.php
2) resources/views/livewire/admin/control/<module_slug>/create.blade.php
3) resources/views/livewire/admin/control/<module_slug>/edit.blade.php
4) The Route::livewire(...) snippet (admin prefix) with the correct route names:
   admin.<module_slug>.index / create / edit
5) Optional: Flux sidebar item snippet that matches existing menu style.

Constraints:
- Don’t invent new layouts or components. Match the reference stubs.
- Keep code direct and readable.
```

#### Prompt 2 — “Make it match the package style exactly”

Use this when AI output looks “close but not consistent”.

```text
Refactor the following Blade+Livewire anonymous component file to match the UI conventions used in this package’s stubs.

Must match:
- <x-admin-panel> shell with header/desc/button slots
- Search card styling + table styling (header bg, zebra rows, dividers)
- Action button colors and spacing consistent with existing stubs
- Dark mode classes present
- Notification rendering: {!! $this->renderNotification() !!}

Here is the reference stub to match:
<paste the relevant stub file content or point to it>

Here is my file to refactor:
<paste file content>
```

#### Prompt 3 — “Generate only the routes/sidebar snippet”

```text
Generate only:
1) the Route::group(['prefix' => 'admin'], ...) entries using Route::livewire(...)
2) the flux:sidebar.item entries for the sidebar

Follow the exact naming conventions used by this package, similar to:
- src/stubs/reference/routes-to-add.php
- src/stubs/reference/sidebar-menu-to-add.blade.php

Module: <module_slug>
Index route name: admin.<module_slug>.index
Create route name: admin.<module_slug>.create
Edit route name: admin.<module_slug>.edit
```

### Example module spec (fill-in template)

Use this to plan quickly before prompting the AI:

```text
Module slug:
Model class:
Fields:
- name: required|string|max:255
- description: nullable|string|max:1000

Index list columns:
- id, name, created_at, is_active

Actions:
- activate(id), deactivate(id), delete(id)
```

### Official docs (reference)

- Livewire docs: [https://livewire.laravel.com/docs](https://livewire.laravel.com/docs)
- Flux docs: [https://fluxui.dev/docs](https://fluxui.dev/docs)

---

## Fortify: passwords, publish, and active users

This package optionally copies `EnsureUserIsActive.php` into your app when Laravel Fortify is installed. This action checks the `is_active` flag on the User model (used by the Vormia package) and blocks inactive users from logging in.

### PasswordValidationRules (admin views)

The admin Livewire stubs expect `App\Actions\Fortify\PasswordValidationRules` (a trait published by Fortify). The `ui-livewireflux-admin:install` command runs `vendor:publish` for `Laravel\Fortify\FortifyServiceProvider` with **`--tag=fortify-support`** when `PasswordValidationRules.php` is not present yet. That publishes action stubs under `app/Actions/Fortify/` (and the app `FortifyServiceProvider` stub) but **does not** publish Fortify database migrations.

### Fortify publish tags (reference)

| Tag | What it publishes |
| --- | --- |
| `fortify-support` | `app/Actions/Fortify/*` stubs (including `PasswordValidationRules`) and `app/Providers/FortifyServiceProvider.php` stub |
| `fortify-migrations` | Migrations for two-factor columns on `users` and the `passkeys` table (and related) — **opt-in**; run only when your schema does not already include them |
| `fortify-config` | `config/fortify.php` |

Install uses **`fortify-support` only**. For optional 2FA/passkeys schema, see [`README.md`](../README.md) (Fortify database section) and publish `fortify-migrations` manually when needed.

### Re-publish or missing stubs

If files under `app/Actions/Fortify/` are missing or incomplete, publish the support tag:

```bash
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --tag=fortify-support
```

If files exist but are wrong or truncated and you intend to **overwrite** Fortify-published stubs (back up or commit first):

```bash
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --tag=fortify-support --force
```

Use **`--tag=fortify-support --force`**, not a bare `--provider ... --force`, because publishing the **whole** provider with `--force` also republishes **`fortify-migrations`**, which can add duplicate migrations and cause `migrate` to fail with duplicate `two_factor_*` columns if those columns already exist.

Use **`--force` carefully**: it replaces published Fortify stub files under your app, which can wipe customizations. Back up or commit your changes before running it.

### What EnsureUserIsActive does

After authentication succeeds, it verifies that the user's `is_active` attribute is `true`. If the user is inactive, it logs them out and throws a validation error: "Your account is disabled."

### Registering the action

You must add `EnsureUserIsActive` to your Fortify authentication pipeline. Open `app/Providers/FortifyServiceProvider.php` and register it in `authenticateThrough`:

```php
use App\Actions\Fortify\EnsureUserIsActive;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;  // if using 2FA

public function boot(): void
{
    // ... configureActions, configureViews, configureRateLimiting ...

    Fortify::authenticateThrough(function () {
        return array_filter([
            config('fortify.limiters.login') ? EnsureLoginIsNotThrottled::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            EnsureUserIsActive::class,  // Add after AttemptToAuthenticate
            PrepareAuthenticatedSession::class,
        ]);
    });
}
```

### Pipeline variations

Your Fortify setup may differ. Common pipeline classes include:

- `EnsureLoginIsNotThrottled` – rate limiting
- `CanonicalizeUsername` – normalize usernames
- `RedirectIfTwoFactorAuthenticatable` – 2FA redirect
- `AttemptToAuthenticate` – credentials check
- `EnsureUserIsActive` – **place after AttemptToAuthenticate**
- `PrepareAuthenticatedSession` – finalize session

Place `EnsureUserIsActive::class` between `AttemptToAuthenticate` and `PrepareAuthenticatedSession` so it runs after the user is authenticated but before the session is prepared.

### User model

Your `User` model must have an `is_active` attribute (boolean). Use the `Vormia\Vormia\Traits\HasVormiaRoles` trait from the Vormia package; the `is_active` column is added by the Vormia package migration. Ensure the column exists and is cast appropriately:

```php
protected function casts(): array
{
    return [
        // ...
        'is_active' => 'boolean',
    ];
}
```

---

## Roles: assign on registration

This package does not modify your authentication flow. To assign a role to new users when they register (e.g., a default "member" role), you need to update your `CreateNewUser` action.

### Prerequisites

- **`app/Actions/Fortify/CreateNewUser.php`** must exist. It is published by Laravel Fortify (for example when you run `php artisan ui-livewireflux-admin:install`, which publishes the `fortify-support` tag when `PasswordValidationRules` is not present yet, or when you run `vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --tag=fortify-support` manually). See [Fortify: passwords, publish, and active users](#fortify-passwords-publish-and-active-users) if files are missing.
- Your `App\Models\User` model must use the `Vormia\Vormia\Traits\HasVormiaRoles` trait from the `vormiaphp/vormia` package.
- Vormia migrations must be run so the `role_user` pivot table exists.

### Implementation

In `app/Actions/Fortify/CreateNewUser.php`, after creating the user, attach the desired role. Reference the Role model from the Vormia package:

```php
use App\Models\User;
use Vormia\Vormia\Models\Role;

public function create(array $input): User
{
    // ... validation ...

    $user = User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => $input['password'],
    ]);

    $defaultRole = Role::where('name', 'user')->first();
    if ($defaultRole) {
        $user->roles()->attach($defaultRole);
    }

    return $user;
}
```

### Using configuration

You can store the default role ID in `config/vormia.php` or `.env` to avoid hardcoding. Use the package's Role model to resolve the role:

```php
use Vormia\Vormia\Models\Role;

$defaultRole = Role::find(config('vormia.default_role_id', 1));
if ($defaultRole) {
    $user->roles()->attach($defaultRole);
}
```

### Admin users

If you want the first registered user (or users from a specific list) to receive an admin role, you can add conditional logic. Prefer looking up roles by name via `Vormia\Vormia\Models\Role`:

```php
use Vormia\Vormia\Models\Role;

// Example: first user gets admin role
$adminRole = Role::where('name', 'admin')->first();
$userRole = Role::where('name', 'user')->first();
$roleToAttach = User::count() === 1 ? $adminRole : ($userRole ?? Role::find(config('vormia.default_role_id', 2)));
if ($roleToAttach) {
    $user->roles()->attach($roleToAttach);
}
```

Alternatively, you can still use role IDs (roles are from `Vormia\Vormia\Models\Role`):

```php
$roleId = User::count() === 1 ? 1 : config('vormia.default_role_id', 2);
$user->roles()->attach($roleId);
```

### Package models reference

When using Vormia models in your app, reference them from the package namespace:

```php
use Vormia\Vormia\Models\Role;
use Vormia\Vormia\Models\Permission;
use Vormia\Vormia\Models\Taxonomy;
use Vormia\Vormia\Models\UserMeta;
```
