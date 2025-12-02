# Vormia UI Livewire Flux Admin

Admin panel components and routes for Laravel applications using Livewire Volt and Flux.

## Requirements

### Required Packages

- **vormiaphp/vormia** - Core Vormia package (REQUIRED)
- **livewire/volt** - Livewire Volt for single-file components (REQUIRED)

### Optional Packages

- **livewire/flux** - For sidebar menu navigation (OPTIONAL - if missing, you'll need to manually add nav links)
- **laravel/fortify** - For user registration (OPTIONAL - if missing, you'll need to manually attach roles)

## Installation

All files to copy are located in the `stubs/` directory, which mirrors the Laravel application structure.

1. Copy `stubs/app/View/Components/AdminPanel.php` to your Laravel application's `app/View/Components/AdminPanel.php`

2. Copy `stubs/resources/views/components/admin-panel.php` to your Laravel application's `resources/views/components/admin-panel.php`

3. Copy all files from `stubs/resources/views/livewire/admin/` to your Laravel application's `resources/views/livewire/admin/` directory

4. Add the routes from `routes-to-add.php` to your `routes/web.php` file inside the `Route::middleware(['auth', 'authority'])->group(function () { ... });` block

5. If you have `livewire/flux` installed, add the sidebar menu code from `sidebar-menu-to-add.php` to your `resources/views/components/layouts/app/sidebar.php` file (after line 20, just after the Dashboard menu item)

6. If you have `laravel/fortify` installed and the file `app/Actions/Fortify/CreateNewUser.php` exists, copy `stubs/app/Actions/Fortify/CreateNewUser.php` to replace your existing one (it includes role attachment)

## Package Structure

```
UILivewireFlux-Admin/
├── stubs/                          # All files to copy to Laravel app
│   ├── app/
│   │   ├── View/
│   │   │   └── Components/
│   │   │       └── AdminPanel.php
│   │   └── Actions/
│   │       └── Fortify/
│   │           └── CreateNewUser.php
│   └── resources/
│       └── views/
│           ├── components/
│           │   └── admin-panel.php
│           └── livewire/
│               └── admin/
│                   ├── admins/
│                   └── control/
│                       ├── availability/
│                       ├── categories/
│                       ├── inheritance/
│                       └── locations/
├── routes-to-add.php               # Routes snippet for routes/web.php
├── sidebar-menu-to-add.php         # Sidebar snippet for sidebar.blade.php
├── composer.json
└── README.md
```

## Routes

The following admin routes will be available:

- Categories: `/admin/categories`, `/admin/categories/create`, `/admin/categories/edit/{id}`
- Inheritance: `/admin/inheritance`, `/admin/inheritance/create`, `/admin/inheritance/edit/{id}`
- Countries: `/admin/countries`, `/admin/countries/create`, `/admin/countries/edit/{id}`
- Cities: `/admin/cities`, `/admin/cities/create`, `/admin/cities/edit/{id}`
- Availability: `/admin/availabilities`, `/admin/availabilities/create`, `/admin/availabilities/edit/{id}`
- Admins: `/admin/admins`, `/admin/admins/create`, `/admin/admins/edit/{id}`

## Sidebar Menu

If `livewire/flux` is installed, add the code from `sidebar-menu-to-add.php` to your sidebar view. The menu items will appear after the Dashboard link.

If `livewire/flux` is NOT installed, you'll need to manually add navigation links to your sidebar.

## User Role Attachment

If `laravel/fortify` is installed and you have `app/Actions/Fortify/CreateNewUser.php`, copy `stubs/app/Actions/Fortify/CreateNewUser.php` which includes automatic role attachment (role ID: 1 for admin).

If `laravel/fortify` is NOT installed, you'll need to manually attach the role to new users:

```php
$user->roles()->attach(1); // 1 for admin
```

## Usage

### AdminPanel Component

Use the AdminPanel component in your views:

```blade
<x-admin-panel
    header="Categories"
    desc="Manage categories"
    :button="$buttonComponent"
/>
```

## License

MIT
