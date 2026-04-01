# AI Guide — Build Consistent Admin UI (Promptbook + Workflow)

This guide is a **copy/paste promptbook** + a **repeatable workflow** for using an AI assistant to build new admin UI screens that match the **flow and style** of `vormiaphp/ui-livewireflux-admin`.

It’s based on `docs/UI-GUIDE.md` and the real stub patterns under:

- `src/stubs/resources/views/livewire/admin/**`

## What “consistent UI” means in this package

Your generated screens should match these conventions:

- **Livewire anonymous components** at the top of each Blade file
- **Layout**: rely on your app’s **default Livewire layout** (no `#[Layout(...)]` attribute)
- **Shell**: wrap content in `<x-admin-panel>` slots (`header`, `desc`, `button`)
- **Notifications**: `use WithNotifications;` and `{!! $this->renderNotification() !!}`
- **Index flow**: search + pagination + table + action buttons
- **Create/Edit flow**: form + validation + success/error notification + “Go back” link
- **Flux sidebar navigation** uses `flux:sidebar.item` with `wire:navigate`

## Workflow (recommended every time)

### Step 0 — Decide the module contract

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

### Step 1 — Give the AI the “style anchors”

Always provide:

- One existing **index** stub (example: `src/stubs/resources/views/livewire/admin/control/categories/index.blade.php`)
- One existing **create/edit** stub (example: the matching `create.blade.php` / `edit.blade.php`)
- The requirement list from `docs/UI-GUIDE.md` (“what matches the package UI”)

This keeps the AI from inventing a different layout or CSS style.

### Step 2 — Generate pages in this order

Ask the AI to build in this order:

1. `index.blade.php`
2. `create.blade.php`
3. `edit.blade.php`
4. Routes snippet to add to `routes/web.php`
5. Sidebar entry snippet (optional)

### Step 3 — Review checklist (don’t skip)

Before you accept the AI output, check:

- **Layout**: pages rely on your app’s default Livewire layout (no `#[Layout(...)]`)
- **Shell**: every page wraps content in `<x-admin-panel>`
- **Notifications**: `{!! $this->renderNotification() !!}` exists and trait is used
- **Dark mode**: uses `dark:*` classes like the stubs
- **Action buttons**: follow the same color semantics (indigo/edit, green/activate, yellow/deactivate, red/delete)
- **Model namespaces**: use package models when required (example from current stubs: `use Vormia\Vormia\Models\Taxonomy;`)
- **App-level classes when matching existing stubs**: admin user screens intentionally use `App\Models\User` and `App\Actions\Fortify\PasswordValidationRules` (Fortify must be published into `app/Actions/Fortify/` — see `docs/FORTIFY-IS-ACTIVE.md`). For brand-new modules under `control/<module>/`, prefer Vormia package models unless the feature truly belongs in `App\...`.

## Promptbook (copy/paste)

### Prompt 1 — Create a new admin module (index/create/edit)

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

### Prompt 2 — “Make it match the package style exactly”

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

### Prompt 3 — “Generate only the routes/sidebar snippet”

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

## Example module spec (fill-in template)

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

## Official docs (reference)

- Livewire docs: `https://livewire.laravel.com/docs`
- Flux docs: `https://fluxui.dev/docs`

