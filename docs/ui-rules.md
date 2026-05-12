---
description: Vormia UI Livewire Flux Admin - package structure, sidebar, AdminPanel, and patterns
globs: "**/*.blade.php,**/*.php"
alwaysApply: false
---

# Vormia UI Livewire Flux Admin — Cursor hints

**Full guide (single doc):** [`docs/GUIDE.md`](GUIDE.md) — UI flow/style, AI prompts, Fortify, roles on registration.

## Project structure (stubs → app after install)

- Admin Livewire views: `resources/views/livewire/admin/`
- Control sections (categories, inheritance, locations, availability): `livewire/admin/control/<module>/`
- Admins: `livewire/admin/admins/`
- Use **Livewire 4 anonymous single-file components** in `.blade.php` (`new class extends Component`), default Livewire layout — **no** `#[Layout(...)]`. Not Volt-specific.

## Sidebar

- Primary: `resources/views/layouts/app/sidebar.blade.php`
- Fallback: `resources/views/components/layouts/app/sidebar.blade.php`
- Inject menu **inside** the Platform `flux:sidebar.group`, before `</flux:sidebar.group>`
- Use `flux:sidebar.item` with `wire:navigate` where the stubs do
- Reference: `src/stubs/reference/sidebar-menu-to-add.blade.php`

## AdminPanel

- Blade: `resources/views/components/admin-panel.blade.php`
- Class: `app/View/Components/AdminPanel.php`
- Slots: `header`, `desc`, `button`, default slot
- Wrap admin pages in `<x-admin-panel>`; use `WithNotifications` and `{!! $this->renderNotification() !!}`
- Match stub **light + dark** Tailwind (`dark:*` classes) — see [`docs/GUIDE.md`](GUIDE.md#ui-flow-and-style)

## Patterns

- `Vormia\Vormia\Traits\Livewire\WithNotifications`; pagination / `#[Validate]` / `#[Computed]` as in stubs
- Route names: `admin.<section>.index`, `.create`, `.edit`
- Password fields in admin-user flows: `App\Actions\Fortify\PasswordValidationRules` after Fortify publish — see [`docs/GUIDE.md`](GUIDE.md#fortify-passwords-publish-and-active-users)
