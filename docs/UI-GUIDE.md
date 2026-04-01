# UI Guide (Flow + Style) — Vormia UI Livewire Flux Admin

This guide is for **developers consuming** `vormiaphp/ui-livewireflux-admin` in their Laravel app and building new admin pages that match the package’s **flow and visual style**.

## What “matches the package UI”

When you add a new admin module, it should follow the same shape as the existing stubs in:

- `src/stubs/resources/views/livewire/admin/**`

The consistent “look & flow” comes from:

- **Layout**: every admin Livewire page uses `#[Layout('layouts.admin')]`
- **Shell**: pages render inside `<x-admin-panel>` with `header`, `desc`, and an optional “action” button slot
- **Patterns**:
  - `index` pages: search, pagination, table list, row actions (edit / activate / deactivate / delete)
  - `create` and `edit` pages: form + validation + notification feedback + “Go back” button
- **Feedback**: pages use the `WithNotifications` trait and render notifications via `{!! $this->renderNotification() !!}`
- **Navigation**: sidebar uses `flux:sidebar.item` entries (see `src/stubs/reference/sidebar-menu-to-add.blade.php`)

## Your mental model (flow)

Most modules follow this exact 3-page flow:

1. **Index**: list records, search, paginate, quick actions
2. **Create**: create a new record, then redirect back or reset the form
3. **Edit**: edit an existing record

Routes are Livewire routes (see `src/stubs/reference/routes-to-add.php`) with names like:

- `admin.<module>.index`
- `admin.<module>.create`
- `admin.<module>.edit`

## Recommended folder structure (when adding a new module)

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

## UI building blocks used by this package

### 1) Admin layout

Every page begins with a PHP block that declares an anonymous Livewire component using the admin layout:

```php
<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Vormia\Vormia\Traits\Livewire\WithNotifications;

new #[Layout('layouts.admin')] class extends Component {
    use WithNotifications;

    // ...
}; ?>
```

### 2) The AdminPanel shell

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

### 3) Tailwind style conventions (as seen in stubs)

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

## Copy/paste templates

### Index page template (list/search/pagination/actions)

Use this as your base. It matches the patterns used by e.g. `control/categories/index.blade.php`.

```php
<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Vormia\Vormia\Traits\Livewire\WithNotifications;

new #[Layout('layouts.admin')] class extends Component {
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

### Create/Edit template (form + validation + “Go back”)

Use the same building blocks the stubs use:

- `#[Validate(...)]` for properties
- `$this->validate()` in `save()` / `update()`
- success/error notifications via `notifySuccess()` / `notifyError()`
- “Go back” button styled like other stubs

## Sidebar + navigation (Flux)

To add your module to the sidebar, follow the same style as:

- `src/stubs/reference/sidebar-menu-to-add.blade.php`

Use `wire:navigate` so navigation stays snappy with Livewire.

## Consistency checklist (before you call it “done”)

- **Routes**: you have `index/create/edit` route names that match the existing conventions
- **Layout**: `#[Layout('layouts.admin')]` is present in all 3 pages
- **Shell**: pages use `<x-admin-panel>` with `header`, `desc`, and a top-right action button (where relevant)
- **Feedback**: `{!! $this->renderNotification() !!}` is present and `WithNotifications` is used
- **Dark mode**: you used `dark:*` classes similar to existing stubs
- **Tables**: `divide-*`, `even:*` zebra rows, and consistent action button colors

## Livewire/Flux docs (official references)

- Livewire docs: `https://livewire.laravel.com/docs`
- Flux docs: `https://fluxui.dev/docs`

