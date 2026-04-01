# Enabling EnsureUserIsActive with Fortify

This package optionally copies `EnsureUserIsActive.php` into your app when Laravel Fortify is installed. This action checks the `is_active` flag on the User model (used by the Vormia package) and blocks inactive users from logging in.

## PasswordValidationRules (admin views)

The admin Livewire stubs expect `App\Actions\Fortify\PasswordValidationRules` (a trait published by Fortify). The `ui-livewireflux-admin:install` command runs `vendor:publish` for `Laravel\Fortify\FortifyServiceProvider` when that file is not present yet.

If you already published Fortify once and **only** `PasswordValidationRules` (or other pieces) are missing, a normal publish may not restore them. You can **re-publish and overwrite** all Fortify-published app files:

```bash
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --force
```

Use **`--force` carefully**: it replaces the published Fortify files under your app (for example `app/Actions/Fortify/*` and related stubs), which can wipe customizations you made in those files. Back up or commit your changes before running it.

## What EnsureUserIsActive Does

After authentication succeeds, it verifies that the user's `is_active` attribute is `true`. If the user is inactive, it logs them out and throws a validation error: "Your account is disabled."

## Registering the Action

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

## Pipeline Variations

Your Fortify setup may differ. Common pipeline classes include:

- `EnsureLoginIsNotThrottled` – rate limiting
- `CanonicalizeUsername` – normalize usernames
- `RedirectIfTwoFactorAuthenticatable` – 2FA redirect
- `AttemptToAuthenticate` – credentials check
- `EnsureUserIsActive` – **place after AttemptToAuthenticate**
- `PrepareAuthenticatedSession` – finalize session

Place `EnsureUserIsActive::class` between `AttemptToAuthenticate` and `PrepareAuthenticatedSession` so it runs after the user is authenticated but before the session is prepared.

## User Model

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
