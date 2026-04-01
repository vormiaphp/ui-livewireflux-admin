# Release Notes

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

