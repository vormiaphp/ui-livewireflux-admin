# Release Notes

## v4.0.2

### What changed

- **Install publishes Fortify app stubs**: `php artisan ui-livewireflux-admin:install` runs `vendor:publish` for `Laravel\Fortify\FortifyServiceProvider` when `app/Actions/Fortify/PasswordValidationRules.php` is missing, so admin views that use `App\Actions\Fortify\PasswordValidationRules` work out of the box. If publish fails or the file is still missing, the installer prints the manual command and points to `docs/FORTIFY-IS-ACTIVE.md` (including optional `--force` re-publish).
- **Documentation**: `docs/FORTIFY-IS-ACTIVE.md` covers `PasswordValidationRules`, install behavior, and careful use of `--force`. `docs/ROLE-ON-REGISTRATION.md` notes that `CreateNewUser.php` comes from Fortify publish. `docs/UI-GUIDE.md` and `docs/AI-GUIDE.md` clarify password forms and when `App\...` Fortify classes are intentional.
- **README**: Changelog section removed (release history lives here and in git tags). Install and verify steps updated for Fortify publish.
- **`ui-rules.mdc`**: Corrected outdated Volt / “light mode only” guidance; aligned with Livewire 4 anonymous components and `dark:*` stubs; points to the docs above.

### Why this release

Consuming apps often lacked Fortify-published `PasswordValidationRules` and related actions, which broke admin user create/edit stubs. Automating Fortify publish during install (with safe skip when already present) reduces that failure mode.

### Upgrade notes

- **New installs**: run `php artisan ui-livewireflux-admin:install` as usual; Fortify stubs publish when needed.
- **Existing apps** missing only `PasswordValidationRules` (or other Fortify files): see `docs/FORTIFY-IS-ACTIVE.md` for normal publish vs `--force` (backup customizations first).

## v4.0.1

### What changed

- **Removed hardcoded Livewire layout from admin stubs**: all admin stub page components no longer use `#[Layout('layouts.admin')]` and now rely on your app’s default Livewire layout.
- **Updated docs + guides**: `README.md`, `docs/UI-GUIDE.md`, and `docs/AI-GUIDE.md` were updated to match the new “default layout” approach.

### Why this release

Projects without a `layouts.admin` view were hitting: `Livewire page component layout view not found: [layouts.admin]`. Removing the attribute prevents that mismatch and lets the consuming app control layout globally.

### Upgrade notes

- If you previously published/copied the admin stub views into your app, re-run your package update/install flow (or manually remove `#[Layout('layouts.admin')]` from those files).

## v4.0.0

### What changed

- **Fixed stub trait import**: Livewire stub views now import `WithNotifications` from the Vormia package namespace:
  - `Vormia\Vormia\Traits\Livewire\WithNotifications`
- **Updated Taxonomy model usage in stubs**: stub views no longer reference `\App\Models\Vrm\Taxonomy`; they now use:
  - `Vormia\Vormia\Models\Taxonomy`
- **Updated MediaForge import in stubs**: stub views now import:
  - `VormiaPHP\Vormia\Facades\MediaForge`
- **Added build guides**:
  - `docs/UI-GUIDE.md` (flow + style guide for building consistent admin UI)
  - `docs/AI-GUIDE.md` (promptbook + workflow for using AI to generate consistent UI)

### Why this release

Fresh installs (or projects without local `App\...` shims) could break because some shipped stub views were referencing app namespaces. This release aligns stub imports and model usage with the package-provided namespaces.

### Upgrade notes

- If you previously copied older stubs into your app, re-run your install/update flow (or manually update your copied files) so your local Livewire views match the new namespaces.

