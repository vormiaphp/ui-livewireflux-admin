# Vormia UI Livewire Flux Admin

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/ui-livewireflux-admin.svg)](https://packagist.org/packages/vormiaphp/ui-livewireflux-admin)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/ui-livewireflux-admin.svg)](https://github.com/vormiaphp/ui-livewireflux-admin)

A Laravel package that provides a complete admin panel solution with pre-built components, routes, and views for managing categories, inheritance, locations, availability, and admin users. Built with Livewire 4 and Flux for a modern, reactive admin interface.

## What is This Package?

**Vormia UI Livewire Flux Admin** is a comprehensive admin panel package for Laravel applications that includes:

- **AdminPanel View Component** - A reusable Blade component for consistent admin panel layouts
- **Pre-configured Admin Routes** - Complete CRUD routes for all admin sections
- **Livewire Components** - Single-file Livewire components for categories, inheritance, locations, availability, and admin management
- **Automatic Sidebar Integration** - Sidebar menu injection (requires livewire/flux)
- **Role Management** - See `docs/ROLE-ON-REGISTRATION.md` for assigning roles on registration

This package is designed to work seamlessly with the Vormia ecosystem and follows Laravel best practices.

## Laravel Compatibility

This package supports:

- **Laravel** ^12.0 or ^13.0
- **PHP** ^8.2

This package runs on **Vormia v5**. Required dependencies (installed automatically when you require this package):

- **vormiaphp/vormia** ^5.0 (no less)
- **livewire/flux** ^1.0
- **laravel/fortify** ^2.0

This package targets **Livewire 4** where Volt is bundled with Livewire. The admin page stubs are Livewire anonymous components (`new class extends Component`) and rely on your app’s **default Livewire layout**.

## Installation Process

### Step 1: Install the Package

```bash
composer require vormiaphp/ui-livewireflux-admin
```

### Step 2: Run the Installation Command

```bash
php artisan ui-livewireflux-admin:install
```

This command will:

1. ✅ Check for required dependencies
2. ✅ Copy package stubs into your application (views, `AdminPanel`, etc.)
3. ✅ Publish Laravel Fortify app actions when `PasswordValidationRules` is not present yet (see `docs/FORTIFY-IS-ACTIVE.md` if you need to re-publish with `--force`)
4. ✅ Inject routes into `routes/web.php`
5. ✅ Inject sidebar menu
6. ✅ Copy `EnsureUserIsActive.php` — register it in Fortify per `docs/FORTIFY-IS-ACTIVE.md`
7. ✅ Clear application caches

### Step 3: Verify Installation

After installation, verify that:

- `app/View/Components/AdminPanel.php` and related views are present
- `app/Actions/Fortify/` contains Fortify-published actions (including `PasswordValidationRules` when the publish step ran)
- Routes were added to `routes/web.php`
- Sidebar menu was added
- Caches were cleared

## Manual Configuration (If Needed)

### Routes Not Injected Automatically

If the routes were not automatically injected into `routes/web.php`, manually add them:

1. Open `routes/web.php`
2. Find the `Route::middleware(['auth'])->group(function () { ... });` block
3. Add the routes from `vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/routes-to-add.php` inside this block

**Example:**

```php
<?php

Route::middleware(['auth'])->group(function () {
    // ... existing routes ...

    // Add admin routes here
    Route::group(['prefix' => 'admin'], function () {
        // Routes from routes-to-add.php
    });
});
```

### Sidebar Menu Not Injected

If the sidebar menu wasn't injected:

1. Open your sidebar file. The package checks (in order): `resources/views/layouts/app/sidebar.blade.php`, then `resources/views/components/layouts/app/sidebar.blade.php`
2. Find the Platform `flux:sidebar.group` (the group containing the Dashboard menu item)
3. Add the code from `vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.blade.php` **inside** the Platform group, before the closing `</flux:sidebar.group>` tag

**Example:**

```blade
<flux:sidebar.group :heading="__('Platform')" class="grid">
    <flux:sidebar.item icon="home" :href="route('dashboard')" wire:navigate>
        {{ __('Dashboard') }}
    </flux:sidebar.item>

    <!-- Add admin menu items here, before the closing tag -->
</flux:sidebar.group>
```

### Role Attachment Not Working

To attach roles to new users (e.g. in registration), use the Role model from the Vormia package (`Vormia\Vormia\Models\Role`) and look up by name:

```php
use Vormia\Vormia\Models\Role;

// In your user registration logic
$user = User::create([...]);
$defaultRole = Role::where('name', 'user')->first();
if ($defaultRole) {
    $user->roles()->attach($defaultRole);
}
```

## What You Get

### 1. AdminPanel Component

A reusable Blade component for consistent admin panel layouts:

```blade
<x-admin-panel
    header="Categories"
    desc="Manage your categories"
    :button="$createButton"
>
    <!-- Your content here -->
</x-admin-panel>
```

**Location:** `app/View/Components/AdminPanel.php` and `resources/views/components/admin-panel.blade.php`

### 2. Admin Routes

Pre-configured routes for all admin sections:

- **Categories:** `/admin/categories`, `/admin/categories/create`, `/admin/categories/edit/{id}`
- **Inheritance:** `/admin/inheritance`, `/admin/inheritance/create`, `/admin/inheritance/edit/{id}`
- **Countries:** `/admin/countries`, `/admin/countries/create`, `/admin/countries/edit/{id}`
- **Cities:** `/admin/cities`, `/admin/cities/create`, `/admin/cities/edit/{id}`
- **Availability:** `/admin/availabilities`, `/admin/availabilities/create`, `/admin/availabilities/edit/{id}`
- **Admins:** `/admin/admins`, `/admin/admins/create`, `/admin/admins/edit/{id}`

All routes are protected by `auth` middleware.

### 3. Livewire Components

Single-file Livewire components for each admin section:

- `resources/views/livewire/admin/admins/` - Admin user management
- `resources/views/livewire/admin/control/categories/` - Category management
- `resources/views/livewire/admin/control/inheritance/` - Inheritance management
- `resources/views/livewire/admin/control/locations/` - Location management (countries/cities)
- `resources/views/livewire/admin/control/availability/` - Availability management

Each section includes:

- `index.blade.php` - List view
- `create.blade.php` - Create form
- `edit.blade.php` - Edit form

### 4. Sidebar Menu Integration

The sidebar automatically includes:

- Countries navigation link
- Cities navigation link
- Availability navigation link
- Inheritance navigation link
- Admins navigation link (for super admins only)

### 5. Role Management

Role models live in the Vormia package (`Vormia\Vormia\Models\Role`). To assign roles to new users on registration, see `docs/ROLE-ON-REGISTRATION.md`.

## What to Be Aware Of

### ⚠️ Important Considerations

1. **File Overwrites**
   - The installation process will copy files to your application
   - If files already exist, you'll be prompted to overwrite them
   - Always backup your files before updating

2. **Middleware Requirements**
   - All admin routes require `auth` middleware
   - Ensure your application has this middleware configured

3. **Role models**
   - Roles are referenced from the Vormia package: `Vormia\Vormia\Models\Role`. See `docs/ROLE-ON-REGISTRATION.md` for how to assign roles on registration.

4. **Sidebar File Location**
   - The package checks (in order): `resources/views/layouts/app/sidebar.blade.php`, then `resources/views/components/layouts/app/sidebar.blade.php`
   - Menu items are inserted inside the Platform `flux:sidebar.group`, before the closing `</flux:sidebar.group>` tag
   - If your sidebar is elsewhere, add the menu manually

5. **Route Injection**
   - Routes are injected into `routes/web.php`
   - The package looks for: `Route::middleware(['auth'])->group(function () { ... });`
   - If this pattern doesn't exist, routes won't be injected automatically

6. **Custom Modifications**
   - Any custom modifications to package files will be lost when updating
   - Consider extending components instead of modifying them directly
   - Backups are created during updates (stored in `storage/app/ui-livewireflux-admin-backups/`)

7. **Dependencies**
   - The package requires `vormiaphp/vormia`, `livewire/flux`, and `laravel/fortify`. This package targets Livewire 4 (Volt is bundled with Livewire).

## Help, Update, and Uninstallation

### Getting Help

Display comprehensive help information:

```bash
php artisan ui-livewireflux-admin:help
```

This command shows:

- Available commands
- Usage examples
- Package features
- Requirements
- Troubleshooting tips

### Checking Dependencies

Verify that all required and optional dependencies are installed:

```bash
php artisan ui-livewireflux-admin:check-dependencies
```

This command checks for:

- ✅ vormiaphp/vormia (required)
- ✅ livewire/flux (required)
- ✅ laravel/fortify (required)

### Updating the Package

Update all package files to the latest version:

```bash
php artisan ui-livewireflux-admin:update
```

**What happens:**

1. Creates a backup of existing files in `storage/app/ui-livewireflux-admin-backups/`
2. Replaces all package files with fresh copies
3. Clears application caches

**Force update (skip confirmation):**

```bash
php artisan ui-livewireflux-admin:update --force
```

**⚠️ Warning:** This will overwrite any custom modifications to package files. Always backup your changes first!

### Uninstalling the Package

Remove all package files and configurations:

```bash
php artisan ui-livewireflux-admin:uninstall
```

**What gets removed:**

- `app/View/Components/AdminPanel.php`
- `app/Actions/Fortify/EnsureUserIsActive.php` (if copied)
- `resources/views/components/admin-panel.blade.php`
- `resources/views/livewire/admin/` directory
- Routes from `routes/web.php`
- Sidebar menu items from `sidebar.blade.php`

**Force uninstall (skip confirmation):**

```bash
php artisan ui-livewireflux-admin:uninstall --force
```

**⚠️ Warning:** This action cannot be undone! Make sure you have backups before uninstalling.

**Manual Route Removal:**

If you need to manually remove routes, simply delete routes based on their names from `routes/web.php`:

- `admin.categories.index`, `admin.categories.create`, `admin.categories.edit`
- `admin.inheritance.index`, `admin.inheritance.create`, `admin.inheritance.edit`
- `admin.countries.index`, `admin.countries.create`, `admin.countries.edit`
- `admin.cities.index`, `admin.cities.create`, `admin.cities.edit`
- `admin.availabilities.index`, `admin.availabilities.create`, `admin.availabilities.edit`
- `admin.admins.index`, `admin.admins.create`, `admin.admins.edit`

**After uninstallation:**

1. Remove the package from `composer.json`:
   ```bash
   composer remove vormiaphp/ui-livewireflux-admin
   ```
2. Run `composer update` to clean up dependencies

## Package Structure

```
UILivewireFlux-Admin/
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       ├── InstallCommand.php
│   │       ├── UpdateCommand.php
│   │       ├── UninstallCommand.php
│   │       ├── HelpCommand.php
│   │       └── CheckDependenciesCommand.php
│   ├── stubs/                          # Files copied to Laravel app
│   │   ├── app/
│   │   │   ├── View/
│   │   │   │   └── Components/
│   │   │   │       └── AdminPanel.php
│   │   │   └── Actions/
│   │   │       └── Fortify/
│   │   │           └── EnsureUserIsActive.php
│   │   └── resources/
│   │       └── views/
│   │           ├── components/
│   │           │   └── admin-panel.blade.php
│   │           └── livewire/
│   │               └── admin/
│   │                   ├── admins/
│   │                   └── control/
│   │                       ├── availability/
│   │                       ├── categories/
│   │                       ├── inheritance/
│   │                       └── locations/
│   │   └── reference/
│   │       ├── routes-to-add.php       # Routes snippet
│   │       └── sidebar-menu-to-add.blade.php # Sidebar snippet
│   ├── UILivewireFlux.php
│   └── UILivewireFluxAdminServiceProvider.php
├── composer.json
└── README.md
```

## Usage Examples

### Using the AdminPanel Component

```blade
<x-admin-panel
    header="Manage Categories"
    desc="Create, edit, and delete categories"
    :button="
        <a href='{{ route('admin.categories.create') }}' class='btn btn-primary'>
            Create Category
        </a>
    "
>
    <!-- Your table or content here -->
    <table>
        <!-- ... -->
    </table>
</x-admin-panel>
```

### Accessing Admin Routes

All routes are accessible via their named routes:

```php
route('admin.categories.index')
route('admin.categories.create')
route('admin.categories.edit', ['id' => 1])
```

## Troubleshooting

### Installation Fails

**Problem:** Installation command fails with dependency errors.

**Solution:**

1. Ensure `vormiaphp/vormia`, `livewire/flux`, and `laravel/fortify` are installed.
2. Run `php artisan ui-livewireflux-admin:check-dependencies` to verify
3. Check that your PHP version is >= 8.2
4. Check that your Laravel version is 12.x or 13.x

### Routes Not Working

**Problem:** Admin routes return 404 or are not accessible.

**Solution:**

1. Verify routes were injected into `routes/web.php`
2. Check that the middleware group exists: `Route::middleware(['auth'])->group(...)`
3. Run `php artisan route:clear` and `php artisan route:cache`
4. Verify you're logged in and have the required permissions

### Sidebar Menu Not Appearing

**Problem:** Sidebar menu items are not visible.

**Solution:**

1. Check if `livewire/flux` is installed: `composer show livewire/flux`
2. Verify the sidebar file exists at `resources/views/layouts/app/sidebar.blade.php` or `resources/views/components/layouts/app/sidebar.blade.php`
3. Add the menu code inside the Platform `flux:sidebar.group` (before `</flux:sidebar.group>`) from `vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.blade.php`
4. Clear view cache: `php artisan view:clear`

### Role Attachment Not Working

**Problem:** New users are not getting the expected role.

**Solution:**

Use `Vormia\Vormia\Models\Role` from the Vormia package and attach by role model (e.g. look up by name). See `docs/ROLE-ON-REGISTRATION.md` for how to update `CreateNewUser` to attach roles on registration.

## License

MIT

## Support

For issues, questions, or contributions, please visit:

- **GitHub:** https://github.com/vormiaphp/ui-livewireflux-admin
- **Packagist:** https://packagist.org/packages/vormiaphp/ui-livewireflux-admin
