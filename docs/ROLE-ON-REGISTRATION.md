# Assigning a Role on User Registration

This package does not modify your authentication flow. To assign a role to new users when they register (e.g., a default "member" role), you need to update your `CreateNewUser` action.

## Prerequisites

- Your `App\Models\User` model must use the Vormia package's roles relationship (e.g. `HasRoles` trait or equivalent from `vormiaphp/vormia`).
- Vormia migrations must be run so the `role_user` pivot table exists.

## Implementation

In `app/Actions/Fortify/CreateNewUser.php`, after creating the user, attach the desired role:

```php
public function create(array $input): User
{
    // ... validation ...

    $user = User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => $input['password'],
    ]);

    // Assign default role (e.g. role ID 1 for Super Admin, or use config)
    $user->roles()->attach(1);

    return $user;
}
```

## Using Configuration

You can store the default role ID in `config/vormia.php` or `.env` to avoid hardcoding:

```php
$defaultRoleId = config('vormia.default_role_id', 1);
$user->roles()->attach($defaultRoleId);
```

## Admin Users

If you want the first registered user (or users from a specific list) to receive an admin role, you can add conditional logic:

```php
// Example: first user gets admin role
$roleId = User::count() === 1 ? 1 : config('vormia.default_role_id', 2);
$user->roles()->attach($roleId);
```
