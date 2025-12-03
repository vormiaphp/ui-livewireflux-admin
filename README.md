# Vormia UI Livewire Flux Admin

A Laravel package that provides a complete admin panel solution with pre-built components, routes, and views for managing categories, inheritance, locations, availability, and admin users. Built with Livewire Volt and Flux for a modern, reactive admin interface.

## What is This Package?

**Vormia UI Livewire Flux Admin** is a comprehensive admin panel package for Laravel applications that includes:

- **AdminPanel View Component** - A reusable Blade component for consistent admin panel layouts
- **Pre-configured Admin Routes** - Complete CRUD routes for all admin sections
- **Livewire Volt Components** - Single-file components for categories, inheritance, locations, availability, and admin management
- **Automatic Sidebar Integration** - Sidebar menu injection (when livewire/flux is installed)
- **Role Management** - Automatic role attachment for new users (when laravel/fortify is installed)

This package is designed to work seamlessly with the Vormia ecosystem and follows Laravel best practices.

## Prerequisites

Before installing this package, ensure you have the following **required** packages installed:

### Required Packages

- **PHP** >= 8.2
- **Laravel Framework** >= 12.0
- **vormiaphp/vormia** >= 2.0 (or ^3.0 or ^4.0)
- **livewire/volt** >= 1.0

Install the required packages:

> **Note:** If `vormiaphp/vormia` is already installed, skip this process. This applies only for standalone installation.

```bash
composer require vormiaphp/vormia
composer require livewire/volt
```

## Optional Packages

The following packages are optional but provide enhanced functionality:

### livewire/flux

**Purpose:** Provides automatic sidebar menu injection with Flux navigation components.

**If installed:**

- Sidebar menu items are automatically added to your sidebar view
- Navigation links use Flux components for consistent styling

**If NOT installed:**

- You'll need to manually add navigation links to your sidebar
- The installation process will warn you and provide instructions

**Installation:**

```bash
composer require livewire/flux
```

### laravel/fortify

**Purpose:** Enables automatic role attachment for newly registered users.

**If installed:**

- The `CreateNewUser` action is automatically updated
- New users are automatically assigned the admin role (ID: 1)

**If NOT installed:**

- You'll need to manually attach roles to new users
- The installation process will warn you and provide instructions

**Installation:**

```bash
composer require laravel/fortify
```

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
2. ✅ Copy all package files to your application
3. ✅ Inject routes into `routes/web.php`
4. ✅ Inject sidebar menu (if livewire/flux is installed)
5. ✅ Update `CreateNewUser` action (if laravel/fortify is installed)
6. ✅ Clear application caches

### Step 3: Verify Installation

After installation, verify that:

- Files were copied to `app/View/Components/AdminPanel.php`
- Routes were added to `routes/web.php`
- Sidebar menu was added (if livewire/flux is installed)
- Caches were cleared

## Manual Configuration (If Needed)

### Routes Not Injected Automatically

If the routes were not automatically injected into `routes/web.php`, manually add them:

1. Open `routes/web.php`
2. Find the `Route::middleware(['auth', 'authority'])->group(function () { ... });` block
3. Add the routes from `vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/routes-to-add.php` inside this block

**Example:**

```php
Route::middleware(['auth', 'authority'])->group(function () {
    // ... existing routes ...

    // Add admin routes here
    Route::group(['prefix' => 'admin'], function () {
        // Routes from routes-to-add.php
    });
});
```

### Sidebar Menu Not Injected

If `livewire/flux` is not installed or the sidebar menu wasn't injected:

1. Open `resources/views/components/layouts/app/sidebar.php`
2. Find the Dashboard menu item
3. Add the code from `vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.php` after the Dashboard menu item

**Example:**

```php
<flux:navlist.item icon="home" :href="route('dashboard')" wire:navigate>
    {{ __('Dashboard') }}
</flux:navlist.item>

<!-- Add admin menu items here -->
```

### Role Attachment Not Working

If `laravel/fortify` is not installed, manually attach roles to new users:

```php
// In your user registration logic
$user = User::create([...]);
$user->roles()->attach(1); // 1 for admin
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

**Location:** `app/View/Components/AdminPanel.php` and `resources/views/components/admin-panel.php`

### 2. Admin Routes

Pre-configured routes for all admin sections:

- **Categories:** `/admin/categories`, `/admin/categories/create`, `/admin/categories/edit/{id}`
- **Inheritance:** `/admin/inheritance`, `/admin/inheritance/create`, `/admin/inheritance/edit/{id}`
- **Countries:** `/admin/countries`, `/admin/countries/create`, `/admin/countries/edit/{id}`
- **Cities:** `/admin/cities`, `/admin/cities/create`, `/admin/cities/edit/{id}`
- **Availability:** `/admin/availabilities`, `/admin/availabilities/create`, `/admin/availabilities/edit/{id}`
- **Admins:** `/admin/admins`, `/admin/admins/create`, `/admin/admins/edit/{id}`

All routes are protected by `auth` and `authority` middleware.

### 3. Livewire Volt Components

Single-file components for each admin section:

- `resources/views/livewire/admin/admins/` - Admin user management
- `resources/views/livewire/admin/control/categories/` - Category management
- `resources/views/livewire/admin/control/inheritance/` - Inheritance management
- `resources/views/livewire/admin/control/locations/` - Location management (countries/cities)
- `resources/views/livewire/admin/control/availability/` - Availability management

Each section includes:

- `index.php` / `index.blade.php` - List view
- `create.php` / `create.blade.php` - Create form
- `edit.php` / `edit.blade.php` - Edit form

### 4. Sidebar Menu Integration

When `livewire/flux` is installed, the sidebar automatically includes:

- Countries navigation link
- Cities navigation link
- Availability navigation link
- Inheritance navigation link
- Admins navigation link (for super admins only)

### 5. Role Management

When `laravel/fortify` is installed, new users are automatically assigned the admin role (ID: 1) upon registration.

## What to Be Aware Of

### ⚠️ Important Considerations

1. **File Overwrites**

   - The installation process will copy files to your application
   - If files already exist, you'll be prompted to overwrite them
   - Always backup your files before updating

2. **Middleware Requirements**

   - All admin routes require `auth` and `authority` middleware
   - Ensure your application has these middleware configured
   - The `authority` middleware should come from the `vormiaphp/vormia` package

3. **Role IDs**

   - The package assumes role ID `1` is the admin role
   - If your application uses different role IDs, you'll need to update:
     - `app/Actions/Fortify/CreateNewUser.php` (if using Fortify)
     - Any custom role attachment logic

4. **Sidebar File Location**

   - The package expects the sidebar at: `resources/views/components/layouts/app/sidebar.php`
   - If your sidebar is in a different location, you'll need to manually add the menu items

5. **Route Injection**

   - Routes are injected into `routes/web.php`
   - The package looks for: `Route::middleware(['auth', 'authority'])->group(function () { ... });`
   - If this pattern doesn't exist, routes won't be injected automatically

6. **Custom Modifications**

   - Any custom modifications to package files will be lost when updating
   - Consider extending components instead of modifying them directly
   - Backups are created during updates (stored in `storage/app/ui-livewireflux-admin-backups/`)

7. **Dependencies**
   - The package requires `vormiaphp/vormia` - installation will fail without it
   - `livewire/flux` and `laravel/fortify` are optional but recommended

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
- ✅ livewire/volt (required)
- ⚠️ livewire/flux (optional)
- ⚠️ laravel/fortify (optional)

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
- `app/Actions/Fortify/CreateNewUser.php` (if it was updated)
- `resources/views/components/admin-panel.php`
- `resources/views/livewire/admin/` directory
- Routes from `routes/web.php`
- Sidebar menu items from `sidebar.php`

**Force uninstall (skip confirmation):**

```bash
php artisan ui-livewireflux-admin:uninstall --force
```

**⚠️ Warning:** This action cannot be undone! Make sure you have backups before uninstalling.

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
│   │   │           └── CreateNewUser.php
│   │   └── resources/
│   │       └── views/
│   │           ├── components/
│   │           │   └── admin-panel.php
│   │           └── livewire/
│   │               └── admin/
│   │                   ├── admins/
│   │                   └── control/
│   │                       ├── availability/
│   │                       ├── categories/
│   │                       ├── inheritance/
│   │                       └── locations/
│   ├── UILivewireFlux.php
│   └── UILivewireFluxAdminServiceProvider.php
├── src/
│   └── stubs/
│       └── reference/
│           ├── routes-to-add.php       # Routes snippet
│           └── sidebar-menu-to-add.php # Sidebar snippet
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

1. Ensure `vormiaphp/vormia` and `livewire/volt` are installed
2. Run `php artisan ui-livewireflux-admin:check-dependencies` to verify
3. Check that your PHP version is >= 8.2
4. Check that your Laravel version is >= 12.0

### Routes Not Working

**Problem:** Admin routes return 404 or are not accessible.

**Solution:**

1. Verify routes were injected into `routes/web.php`
2. Check that the middleware group exists: `Route::middleware(['auth', 'authority'])->group(...)`
3. Run `php artisan route:clear` and `php artisan route:cache`
4. Verify you're logged in and have the required permissions

### Sidebar Menu Not Appearing

**Problem:** Sidebar menu items are not visible.

**Solution:**

1. Check if `livewire/flux` is installed: `composer show livewire/flux`
2. Verify the sidebar file exists at: `resources/views/components/layouts/app/sidebar.php`
3. Manually add the menu code from `vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.php` if needed
4. Clear view cache: `php artisan view:clear`

### Role Attachment Not Working

**Problem:** New users are not getting the admin role.

**Solution:**

1. Check if `laravel/fortify` is installed
2. Verify `app/Actions/Fortify/CreateNewUser.php` exists and was updated
3. Check that the role ID `1` exists in your database
4. Manually add role attachment if needed

## License

MIT

## Support

For issues, questions, or contributions, please visit:

- **GitHub:** https://github.com/vormiaphp/ui-livewireflux-admin
- **Packagist:** https://packagist.org/packages/vormiaphp/ui-livewireflux-admin
