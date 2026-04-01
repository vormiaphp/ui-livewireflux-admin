# Assigning a Role on User Registration

This package does not modify your authentication flow. To assign a role to new users when they register (e.g., a default "member" role), you need to update your `CreateNewUser` action.

## Prerequisites

- **`app/Actions/Fortify/CreateNewUser.php`** must exist. It is published by Laravel Fortify (for example when you run `php artisan ui-livewireflux-admin:install`, which publishes Fortify stubs when `PasswordValidationRules` is not present yet, or when you run `vendor:publish` for `Laravel\Fortify\FortifyServiceProvider` manually). See `docs/FORTIFY-IS-ACTIVE.md` if files are missing.
- Your `App\Models\User` model must use the `Vormia\Vormia\Traits\HasVormiaRoles` trait from the `vormiaphp/vormia` package.
- Vormia migrations must be run so the `role_user` pivot table exists.

## Implementation

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

## Using Configuration

You can store the default role ID in `config/vormia.php` or `.env` to avoid hardcoding. Use the package's Role model to resolve the role:

```php
use Vormia\Vormia\Models\Role;

$defaultRole = Role::find(config('vormia.default_role_id', 1));
if ($defaultRole) {
    $user->roles()->attach($defaultRole);
}
```

## Admin Users

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

## Package models reference

When using Vormia models in your app, reference them from the package namespace:

```php
use Vormia\Vormia\Models\Role;
use Vormia\Vormia\Models\Permission;
use Vormia\Vormia\Models\Taxonomy;
use Vormia\Vormia\Models\UserMeta;
```
