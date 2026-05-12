# Release Notes

## v4.1.0

### What changed

- **Composer `require`**: `livewire/livewire` is now **^4.0** (previously **^4.1**), so apps on Livewire 4.0.x satisfy the dependency without being forced to 4.1+ for this package alone.

### Upgrade notes

- No stub or API changes. If you already use Livewire 4.1 or newer, behavior is unchanged. If `composer update` previously failed because you stayed on Livewire 4.0.x, try again after upgrading to this release.

## v4.0.4

### What changed

- **Documentation**: Merged `docs/UI-GUIDE.md`, `docs/AI-GUIDE.md`, `docs/FORTIFY-IS-ACTIVE.md`, `docs/ROLE-ON-REGISTRATION.md`, and the Cursor-hints file (`docs/ui-rules.md` / earlier `ui-rules.mdc`) into a single [`docs/GUIDE.md`](docs/GUIDE.md). It includes a table of contents, section anchors, and YAML frontmatter (`description`, `globs`, `alwaysApply`) at the top for use as a Cursor project rule.
- **References**: `README.md`, install/help commands, and `EnsureUserIsActive` stub comments now point to `docs/GUIDE.md` (with hash fragments where helpful).

### Upgrade notes

- If you bookmarked the old doc paths, use `docs/GUIDE.md` and the headings **UI**, **AI**, **Fortify**, or **Roles** (anchors match the old topics).
- If you used `docs/ui-rules.md` (or `ui-rules.mdc`) as a Cursor rule, point Project Rules at `docs/GUIDE.md` instead; the frontmatter and quick reference now live there.

## v4.0.3

### What changed

- **Composer `require`**: Raised minimum supported versions to align with the current stack: `vormiaphp/vormia` ^5.4, `livewire/livewire` ^4.1, `livewire/flux` ^2.13.1, `laravel/fortify` ^1.34 (Flux 1.x and Fortify 2.x are no longer in the declared range).
- **README**: Dependency bullets and troubleshooting text updated to match.

### Upgrade notes

- Before upgrading, bump consuming apps to at least those versions (or compatible newer minors within the same major lines). Run `composer update` and resolve any conflicts if you were on Flux 1, Fortify 2, Vormia below 5.4, or Livewire below 4.1.

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

