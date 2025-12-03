# AI/LLM Development Guide - Vormia UI Livewire Flux Admin

This guide helps AI assistants understand the structure, patterns, and conventions used in this Laravel package for building admin interfaces.

## Table of Contents

1. [Project Structure](#project-structure)
2. [Sidebar Location](#sidebar-location)
3. [AdminPanel Layout Component](#adminpanel-layout-component)
4. [Building Create Forms](#building-create-forms)
5. [Building Index/Table Views](#building-indextable-views)
6. [Building Edit Forms](#building-edit-forms)
7. [File Naming Conventions](#file-naming-conventions)
8. [Folder Structure Rules](#folder-structure-rules)
9. [Common Patterns](#common-patterns)

---

## Project Structure

### Key Directories

```
src/stubs/resources/views/
├── components/
│   └── admin-panel.blade.php          # Main layout component
└── livewire/
    └── admin/                          # All admin components go here
        ├── admins/                     # Admin user management
        └── control/                    # Control/management sections
            ├── availability/
            ├── categories/
            ├── inheritance/
            └── locations/
```

**Important:** All admin-related Livewire Volt components should be placed in `resources/views/livewire/admin/` directory.

---

## Sidebar Location

### Where is the Sidebar?

The sidebar is located at:
```
resources/views/components/layouts/app/sidebar.blade.php
```

### How Sidebar Works

- The sidebar is automatically injected during package installation (if `livewire/flux` is installed)
- Sidebar menu items are added from: `src/stubs/reference/sidebar-menu-to-add.blade.php`
- Menu items use Flux navigation components: `<flux:navlist.item>`
- Routes are checked using: `request()->routeIs('admin.*')`

### Sidebar Menu Pattern

```php
<flux:navlist.item icon="map-pin" :href="route('admin.countries.index')"
    :current="request()->routeIs('admin.countries.*')" wire:navigate>
    {{ __('Countries') }}
</flux:navlist.item>
```

**Note:** The sidebar file location is fixed. Do not create alternative sidebar locations unless explicitly requested.

---

## AdminPanel Layout Component

### Location

```
src/stubs/resources/views/components/admin-panel.blade.php
```

### Component Class

```
src/stubs/app/View/Components/AdminPanel.php
```

### Usage Pattern

The `AdminPanel` component provides a consistent layout wrapper for all admin pages.

#### Basic Structure

```blade
<x-admin-panel>
    <x-slot name="header">{{ __('Page Title') }}</x-slot>
    <x-slot name="desc">
        {{ __('Page description text') }}
    </x-slot>
    <x-slot name="button">
        <!-- Optional action button (e.g., "Go Back", "Add New") -->
    </x-slot>

    <!-- Your page content here -->
    {{ $slot }}
</x-admin-panel>
```

#### Component Slots

1. **`header`** (optional): Page title/heading
2. **`desc`** (optional): Page description text
3. **`button`** (optional): Action button (typically "Go Back" or "Add New")
4. **`$slot`**: Main content area

#### Example: Create Page Header

```blade
<x-admin-panel>
    <x-slot name="header">{{ __('Add New Category') }}</x-slot>
    <x-slot name="desc">
        {{ __('Add a new category to the mobile app.') }}
    </x-slot>
    <x-slot name="button">
        <a href="{{ route('admin.categories.index') }}"
            class="bg-black text-white hover:bg-gray-800 px-3 py-2 rounded-md float-right text-sm font-bold">
            Go Back
        </a>
    </x-slot>

    <!-- Form content -->
</x-admin-panel>
```

#### Example: Index Page Header

```blade
<x-admin-panel>
    <x-slot name="header">{{ __('Categories') }}</x-slot>
    <x-slot name="desc">
        {{ __('Manage the categories displayed in the mobile app.') }}
    </x-slot>
    <x-slot name="button">
        <a href="{{ route('admin.categories.create') }}"
            class="bg-blue-500 text-white hover:bg-blue-600 px-3 py-2 rounded-md float-right text-sm font-bold">
            Add New Category
        </a>
    </x-slot>

    <!-- Table content -->
</x-admin-panel>
```

### Important Notes

- **Always wrap admin page content** in `<x-admin-panel>` component
- **Do NOT use dark mode classes** - the component uses light mode styling
- The component provides consistent spacing, separators, and layout structure
- Content is automatically placed in a scrollable container

---

## Building Create Forms

### File Location Pattern

```
resources/views/livewire/admin/{section}/create.blade.php
```

### Complete Create Form Structure

```blade
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    public function save(): void
    {
        $this->validate();

        try {
            // Create logic here
            $_taxonomy = new \App\Models\Vrm\Taxonomy();
            $_taxonomy->name = $this->name;
            $_taxonomy->type = 'your-type';
            $_taxonomy->save();

            // Reset form
            $this->reset(['name', 'description']);

            // Success notification
            $this->notifySuccess(__('Item created successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to create item. Please try again: ' . $e->getMessage()));
        }
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description']);
        $this->notifyInfo(__('Creation cancelled!'));
    }
}; ?>

<div>
    <x-admin-panel>
        <x-slot name="header">{{ __('Add New Item') }}</x-slot>
        <x-slot name="desc">
            {{ __('Add a new item to the system.') }}
        </x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.items.index') }}"
                class="bg-black text-white hover:bg-gray-800 px-3 py-2 rounded-md float-right text-sm font-bold">
                Go Back
            </a>
        </x-slot>

        {{-- Form Container --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
            {{-- Display notifications --}}
            {!! $this->renderNotification() !!}

            <form wire:submit="save">
                <div class="space-y-12">
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        {{-- Left Column: Field Descriptions --}}
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Field Name</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">
                                Description of what this field is for.
                            </p>
                        </div>

                        {{-- Right Column: Form Fields --}}
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            {{-- Text Input Field --}}
                            <div class="col-span-full">
                                <label for="name" class="block text-sm/6 font-medium text-gray-900 required">Name</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="text" id="name" wire:model="name" placeholder="e.g. Example Name"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('name') }}</span>
                                </div>
                            </div>

                            {{-- Textarea Field --}}
                            <div class="col-span-full">
                                <label for="description" class="block text-sm/6 font-medium text-gray-900">Description</label>
                                <div class="mt-2">
                                    <textarea id="description" wire:model="description" rows="3"
                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('description') }}</span>
                                </div>
                                <p class="mt-3 text-sm/6 text-gray-600">Write a description for the item.</p>
                            </div>

                            {{-- Form Actions --}}
                            <div class="col-span-full">
                                <div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 pt-4">
                                    <button type="button" wire:click="cancel"
                                        class="text-sm font-semibold text-gray-900">Cancel</button>

                                    <button type="submit" wire:loading.attr="disabled"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        <span wire:loading.remove>Save</span>
                                        <span wire:loading>Saving...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </x-admin-panel>
</div>
```

### Form Field Patterns

#### Text Input

```blade
<div class="col-span-full">
    <label for="field_name" class="block text-sm/6 font-medium text-gray-900 required">Field Label</label>
    <div class="mt-2">
        <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
            <input type="text" id="field_name" wire:model="field_name" placeholder="Placeholder text"
                class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
        </div>
        <span class="text-red-500 text-sm italic"> {{ $errors->first('field_name') }}</span>
    </div>
</div>
```

#### Textarea

```blade
<div class="col-span-full">
    <label for="description" class="block text-sm/6 font-medium text-gray-900">Description</label>
    <div class="mt-2">
        <textarea id="description" wire:model="description" rows="3"
            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
        <span class="text-red-500 text-sm italic"> {{ $errors->first('description') }}</span>
    </div>
    <p class="mt-3 text-sm/6 text-gray-600">Helper text for the field.</p>
</div>
```

#### File Upload

```blade
<div class="col-span-full">
    <label for="photo" class="block text-sm/6 font-medium text-gray-900">Photo</label>
    <div class="mt-2 flex items-center gap-x-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
        <div>
            <input type="file" wire:model="picture" accept=".jpg,.jpeg,.png,.webp"
                class="block w-full cursor-pointer px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-gray-200 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-gray-900 hover:file:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
            <span class="text-red-500 text-sm italic"> {{ $errors->first('picture') }}</span>
        </div>
    </div>

    {{-- Preview uploaded image --}}
    @if ($picture)
        <div class="mt-2">
            <img src="{{ $picture->temporaryUrl() }}" alt="Preview" class="h-20 w-20 object-cover rounded-md">
        </div>
    @endif
</div>
```

### Key Points for Create Forms

1. **Always use `WithNotifications` trait** for success/error messages
2. **Use `#[Validate]` attributes** for field validation
3. **Include `cancel()` method** to reset form
4. **Use `wire:submit="save"`** for form submission
5. **Show loading states**: `wire:loading.remove` and `wire:loading`
6. **Display errors**: `{{ $errors->first('field_name') }}`
7. **Use notification rendering**: `{!! $this->renderNotification() !!}`

---

## Building Index/Table Views

### File Location Pattern

```
resources/views/livewire/admin/{section}/index.blade.php
```

### Complete Index/Table Structure

```blade
<?php

use Livewire\WithPagination;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithPagination;
    use WithNotifications;

    public $search = '';
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    #[Computed]
    public function results()
    {
        $query = \App\Models\Vrm\Taxonomy::query();
        $query->with('slugs');
        $query->where('type', 'your-type');

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderBy('created_at', 'desc');
        return $query->paginate($this->perPage);
    }

    // Activate
    public function activate($id): void
    {
        $item = \App\Models\Vrm\Taxonomy::find($id);
        if ($item) {
            $item->is_active = true;
            $item->save();
            $this->notifySuccess(__('Item was activated successfully!'));
        } else {
            $this->notifyError(__('Item not found!'));
        }
    }

    // Deactivate
    public function deactivate($id): void
    {
        $item = \App\Models\Vrm\Taxonomy::find($id);
        if ($item) {
            $item->is_active = false;
            $item->save();
            $this->notifySuccess(__('Item was deactivated successfully!'));
        } else {
            $this->notifyError(__('Item not found!'));
        }
    }

    // Delete
    public function delete($id): void
    {
        try {
            $item = \App\Models\Vrm\Taxonomy::find($id);
            if ($item) {
                $this->notifySuccess(__('Item was deleted successfully!'));
                $item->delete();
            } else {
                $this->notifyError(__('Item not found!'));
            }
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to delete item: ' . $e->getMessage()));
        }
    }
}; ?>

<div>
    <x-admin-panel>
        <x-slot name="header">{{ __('Items') }}</x-slot>
        <x-slot name="desc">
            {{ __('Manage the items displayed in the application.') }}
            {{ __('You can create, edit, enable/disable, or delete items here.') }}
        </x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.items.create') }}"
                class="bg-blue-500 text-white hover:bg-blue-600 px-3 py-2 rounded-md float-right text-sm font-bold">
                Add New Item
            </a>
        </x-slot>

        {{-- Search & Filter --}}
        <div class="my-4">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold text-gray-900">Search & Filter data</h3>
                    <form class="sm:flex sm:items-center">
                        <div class="w-full sm:max-w-xs">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                placeholder="Search items..." />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Display notifications --}}
        {!! $this->renderNotification() !!}

        {{-- Table --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg mt-2">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">#ID</th>
                        <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @if ($this->results->isNotEmpty())
                        @foreach ($this->results as $row)
                            <tr class="even:bg-gray-50">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $row->id }}</td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $row->name }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $row->type }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                    @if ($row->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-green-400 text-white">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-red-400 text-white">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                    {{-- Edit Button --}}
                                    <a href="{{ route('admin.items.edit', $row->id) }}"
                                        class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500">
                                        Edit
                                    </a>

                                    {{-- Activate Button --}}
                                    <button type="button" wire:click="activate({{ $row->id }})"
                                        class="inline-flex items-center gap-x-1.5 rounded-md bg-green-600 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-green-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                            <path fill-rule="evenodd"
                                                d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    {{-- Deactivate Button --}}
                                    <button type="button" wire:click="deactivate({{ $row->id }})"
                                        class="cursor-pointer inline-flex items-center gap-x-1.5 rounded-md bg-yellow-400 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-yellow-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                            <path fill-rule="evenodd"
                                                d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    {{-- Delete Button --}}
                                    <button type="button" wire:click="$js.confirmDelete({{ $row->id }})"
                                        class="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-red-500">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="even:bg-gray-50">
                            <td colspan="5"
                                class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3 text-center">
                                <span class="text-gray-500 text-2xl font-bold">No results found</span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            @if ($this->results->hasPages())
                <div class="p-2">
                    {{ $this->results->links() }}
                </div>
            @endif
        </div>
    </x-admin-panel>

    @script
        <script>
            $js('confirmDelete', (id) => {
                Swal.fire({
                    title: 'Are you sure you want to delete?',
                    text: "This item will be removed permanently.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.delete(id);
                    }
                });
            });
        </script>
    @endscript
</div>
```

### Table Structure Pattern

#### Table Container

```blade
<div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg mt-2">
    <table class="min-w-full divide-y divide-gray-300">
        <!-- Table content -->
    </table>
</div>
```

#### Table Header

```blade
<thead class="bg-gray-50">
    <tr>
        <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">Column Name</th>
        <!-- More columns -->
    </tr>
</thead>
```

#### Table Body

```blade
<tbody class="bg-white">
    @if ($this->results->isNotEmpty())
        @foreach ($this->results as $row)
            <tr class="even:bg-gray-50">
                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                    {{ $row->field_name }}
                </td>
            </tr>
        @endforeach
    @else
        <tr class="even:bg-gray-50">
            <td colspan="5" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3 text-center">
                <span class="text-gray-500 text-2xl font-bold">No results found</span>
            </td>
        </tr>
    @endif
</tbody>
```

#### Status Badge

```blade
@if ($row->is_active)
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-green-400 text-white">
        Active
    </span>
@else
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-red-400 text-white">
        Inactive
    </span>
@endif
```

#### Action Buttons

```blade
<td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
    {{-- Edit --}}
    <a href="{{ route('admin.items.edit', $row->id) }}"
        class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500">
        Edit
    </a>

    {{-- Activate --}}
    <button type="button" wire:click="activate({{ $row->id }})"
        class="inline-flex items-center gap-x-1.5 rounded-md bg-green-600 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-green-500">
        <!-- SVG icon -->
    </button>

    {{-- Deactivate --}}
    <button type="button" wire:click="deactivate({{ $row->id }})"
        class="inline-flex items-center gap-x-1.5 rounded-md bg-yellow-400 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-yellow-500">
        <!-- SVG icon -->
    </button>

    {{-- Delete --}}
    <button type="button" wire:click="$js.confirmDelete({{ $row->id }})"
        class="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-red-500">
        Delete
    </button>
</td>
```

### Key Points for Index/Table Views

1. **Always use `WithPagination` trait** for paginated results
2. **Use `#[Computed]` attribute** for the `results()` method
3. **Include search functionality** with `wire:model.live.debounce.300ms="search"`
4. **Implement `activate()`, `deactivate()`, and `delete()` methods**
5. **Use `$this->results`** to access paginated data
6. **Always show "No results found"** when table is empty
7. **Include pagination links** at the bottom
8. **Use JavaScript confirmation** for delete actions (SweetAlert)

---

## Building Edit Forms

### File Location Pattern

```
resources/views/livewire/admin/{section}/edit.blade.php
```

### Edit Form Structure

Edit forms are similar to create forms, but with these differences:

1. **Include `mount($id)` method** to load existing data
2. **Use `update()` method** instead of `save()`
3. **Show current values** in form fields
4. **Display current photo/file** if exists
5. **Allow removing existing files**

#### Key Differences from Create

```php
// Edit form includes mount method
public function mount($id)
{
    $this->item_id = $id;
    $this->item = \App\Models\Vrm\Taxonomy::find($this->item_id);

    if ($this->item) {
        $this->name = $this->item->name;
        $this->description = $this->item->getMeta('description');
        $this->currentPhoto = $this->item->getMeta('picture');
    }
}

// Update method instead of save
public function update()
{
    $this->validate();
    // Update logic
    $this->notifySuccess(__('Item updated successfully!'));
}
```

#### Display Current Photo/File

```blade
@if (!$picture && $currentPhoto)
    <div class="col-span-full">
        <div class="bg-gray-100 rounded-md">
            <div class="grid grid-cols-2 gap-0.5 overflow-hidden sm:rounded-2xl md:grid-cols-3 py-8 sm:py-10">
                <div class="bg-white/5">
                    <img width="120" src="{{ asset($currentPhoto) }}" alt="Current"
                        class="max-h-12 w-full object-contain" />
                </div>
                <button type="button" wire:click="removePhoto('{{ $currentPhoto }}')"
                    class="cursor-pointer rounded-sm bg-red-600 px-2 py-0.5 text-xs font-semibold text-white shadow-xs hover:bg-red-500">
                    Remove current image
                </button>
            </div>
        </div>
    </div>
@endif
```

---

## File Naming Conventions

### Livewire Volt Components

All admin components use the **single-file Volt pattern** (PHP class + Blade template in one `.blade.php` file):

- **Create page**: `create.blade.php`
- **Edit page**: `edit.blade.php`
- **Index/List page**: `index.blade.php`

**Important:** Do NOT create separate `.php` files. The Volt component class and Blade template are in the same `.blade.php` file.

### Component Structure

```blade
<?php
// PHP component class at the top
new class extends Component {
    // Component logic
}; ?>

<!-- Blade template below -->
<div>
    <!-- HTML/Blade content -->
</div>
```

---

## Folder Structure Rules

### Default Structure

**All admin components go in:**
```
resources/views/livewire/admin/{section}/
```

### Examples

✅ **Correct:**
- `resources/views/livewire/admin/products/create.blade.php`
- `resources/views/livewire/admin/users/index.blade.php`
- `resources/views/livewire/admin/settings/edit.blade.php`

❌ **Avoid (unless explicitly requested):**
- `resources/views/livewire/admin/admin/products/` (double "admin")
- `resources/views/livewire/admin/control/products/` (unless it's a control/management section)

### When to Use `/control/` Subdirectory

The `/control/` subdirectory is used for **taxonomy/management sections**:
- Categories
- Inheritance
- Locations
- Availability

**Use `/control/` only when:**
- Managing taxonomy/classification data
- The section is part of system controls
- Explicitly requested by the user

**For regular admin sections, place directly in `/admin/`:**
- Users management → `/admin/users/`
- Products → `/admin/products/`
- Settings → `/admin/settings/`

---

## Common Patterns

### 1. Notifications

Always use the `WithNotifications` trait:

```php
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;
    
    public function someAction()
    {
        $this->notifySuccess(__('Success message'));
        $this->notifyError(__('Error message'));
        $this->notifyInfo(__('Info message'));
    }
}
```

Display notifications in the view:
```blade
{!! $this->renderNotification() !!}
```

### 2. Form Validation

Use `#[Validate]` attributes:

```php
#[Validate('required|string|max:255')]
public $name = '';

#[Validate('nullable|string|max:1000')]
public $description = '';
```

### 3. File Uploads

Use `WithFileUploads` trait:

```php
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $picture;
}
```

### 4. Pagination

Use `WithPagination` trait and `#[Computed]`:

```php
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;
    
    #[Computed]
    public function results()
    {
        return Model::query()->paginate($this->perPage);
    }
}
```

### 5. Search Functionality

```php
public $search = '';

public function updatedSearch()
{
    $this->resetPage();
}
```

```blade
<input type="text" wire:model.live.debounce.300ms="search"
    placeholder="Search items..." />
```

### 6. Route Naming

Follow this pattern:
- Index: `admin.{section}.index`
- Create: `admin.{section}.create`
- Edit: `admin.{section}.edit`

Example:
```php
route('admin.categories.index')
route('admin.categories.create')
route('admin.categories.edit', ['id' => 1])
```

---

## Styling Guidelines

### Color Scheme

- **Primary actions**: `bg-indigo-600` / `hover:bg-indigo-500`
- **Success/Activate**: `bg-green-600` / `hover:bg-green-500`
- **Warning/Deactivate**: `bg-yellow-400` / `hover:bg-yellow-500`
- **Danger/Delete**: `bg-red-600` / `hover:bg-red-500`
- **Secondary/Back**: `bg-black` / `hover:bg-gray-800`
- **Add New**: `bg-blue-500` / `hover:bg-blue-600`

### Text Colors

- **Primary text**: `text-gray-900`
- **Secondary text**: `text-gray-600` or `text-gray-500`
- **Error text**: `text-red-500`
- **Labels**: `text-gray-900` with `font-medium`

### Status Badges

- **Active**: `bg-green-400 text-white`
- **Inactive**: `bg-red-400 text-white`

### Important: No Dark Mode

**DO NOT use dark mode classes** such as:
- `dark:bg-gray-800`
- `dark:text-white`
- `dark:border-gray-700`

The package uses light mode styling only. All components should use light mode classes.

---

## Quick Reference Checklist

When creating a new admin section:

- [ ] Place files in `resources/views/livewire/admin/{section}/`
- [ ] Use single-file Volt components (`.blade.php` only)
- [ ] Wrap content in `<x-admin-panel>` component
- [ ] Include header, desc, and button slots
- [ ] Use `WithNotifications` trait for messages
- [ ] Use `#[Validate]` attributes for validation
- [ ] Include search functionality in index pages
- [ ] Use pagination for list views
- [ ] Implement activate/deactivate/delete methods
- [ ] Use light mode styling only (no dark mode)
- [ ] Follow the table structure pattern
- [ ] Include proper error handling
- [ ] Add route names following the pattern

---

## Example: Complete New Section

### 1. Create Routes (in src/stubs/reference/routes-to-add.php)

```php
Route::group(['prefix' => 'admin'], function () {
    Volt::route('products', 'admin.products.index')->name('admin.products.index');
    Volt::route('products/create', 'admin.products.create')->name('admin.products.create');
    Volt::route('products/edit/{id}', 'admin.products.edit')->name('admin.products.edit');
});
```

### 2. Create Files

- `resources/views/livewire/admin/products/index.blade.php`
- `resources/views/livewire/admin/products/create.blade.php`
- `resources/views/livewire/admin/products/edit.blade.php`

### 3. Follow Patterns

- Use the AdminPanel component
- Follow the form structure from create.blade.php examples
- Follow the table structure from index.blade.php examples
- Use the edit structure from edit.blade.php examples

---

This guide should help AI assistants understand the structure and create consistent admin interfaces following the package's conventions.

