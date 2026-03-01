<?php

namespace Vormia\UILivewireFluxAdmin\Console\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Vormia\UILivewireFluxAdmin\UILivewireFlux;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui-livewireflux-admin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install UI Livewire Flux Admin package with all necessary files and configurations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Installing UI Livewire Flux Admin Package...');

        // Check for required dependencies
        $this->checkRequiredDependencies();

        $vormia = new UILivewireFlux();

        // Step 1: Copy stubs
        $this->step('Copying files from stubs...');
        if ($vormia->install()) {
            $this->info('✅ Files copied successfully.');
        } else {
            $this->error('❌ Failed to copy files.');
            return 1;
        }

        // Step 2: Inject routes
        $this->step('Injecting routes into routes/web.php...');
        $this->injectRoutes();

        // Step 3: Inject sidebar menu (if livewire/flux exists)
        if (InstalledVersions::isInstalled('livewire/flux')) {
            $this->step('Injecting sidebar menu...');
            $this->injectSidebarMenu();
        } else {
            $this->warn('⚠️  livewire/flux is not installed. Sidebar menu will not be automatically injected.');
            $this->line('   You will need to manually add the navigation links to resources/views/layouts/app/sidebar.blade.php (or components/layouts/app/sidebar.blade.php)');
        }

        // Step 4: Copy EnsureUserIsActive (if laravel/fortify exists)
        if (InstalledVersions::isInstalled('laravel/fortify')) {
            $this->step('Copying EnsureUserIsActive action...');
            $this->copyEnsureUserIsActiveOnly();
        } else {
            $this->warn('⚠️  laravel/fortify is not installed. EnsureUserIsActive will not be copied.');
        }

        // Step 5: Clear caches
        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->displayCompletionMessage();

        return 0;
    }

    /**
     * Check for required dependencies
     */
    private function checkRequiredDependencies(): void
    {
        $this->step('Checking required dependencies...');

        $required = [
            'vormiaphp/vormia',
            'livewire/volt',
        ];

        $allGood = true;
        foreach ($required as $package) {
            if (InstalledVersions::isInstalled($package)) {
                $this->info("  ✅ {$package}");
            } else {
                $this->error("  ❌ {$package} - MISSING");
                $this->line("     Please install it first: composer require {$package}");
                $allGood = false;
            }
        }

        if (!$allGood) {
            $this->error('❌ Required dependencies are missing. Please install them before continuing.');
            exit(1);
        }
    }

    /**
     * Display a step message
     */
    private function step($message)
    {
        $this->info("📦 {$message}");
    }

    /**
     * Inject routes into routes/web.php
     */
    private function injectRoutes(): void
    {
        $routesPath = base_path('routes/web.php');
        $routesToAdd = base_path('vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/routes-to-add.php');

        // If developing locally, use local path
        if (!File::exists($routesToAdd)) {
            $routesToAdd = __DIR__ . '/../../stubs/reference/routes-to-add.php';
        }

        if (!File::exists($routesPath)) {
            $this->error('❌ routes/web.php not found.');
            return;
        }

        if (!File::exists($routesToAdd)) {
            $this->error('❌ routes-to-add.php not found.');
            return;
        }

        $content = File::get($routesPath);
        $routesContent = File::get($routesToAdd);

        // Extract just the Route::group part (remove PHP tags and comments)
        $routesContent = preg_replace('/^<\?php\s*/', '', $routesContent);
        $routesContent = preg_replace('/\/\/.*$/m', '', $routesContent);
        $routesContent = trim($routesContent);

        // Check if routes already exist - check for multiple markers
        $routeMarkers = [
            'admin.categories.index',
            'admin.countries.index',
            'admin.availabilities.index',
            'admin.admins.index',
            "Route::group(['prefix' => 'admin']",
        ];

        $routesExist = false;
        foreach ($routeMarkers as $marker) {
            if (strpos($content, $marker) !== false) {
                $routesExist = true;
                break;
            }
        }

        if ($routesExist) {
            $this->warn('⚠️  Routes already exist in routes/web.php. Skipping route injection.');
            return;
        }

        // Find the middleware group - try multiple patterns (auth, auth+verified)
        $middlewarePatterns = [
            '/(Route::middleware\(\[\s*[\'"]auth[\'"]\s*,\s*[\'"]verified[\'"]\s*\]\)->group\(function\s*\(\)\s*\{)/s',
            '/(Route::middleware\(\[[\'"]auth[\'"]\]\)->group\(function\s*\(\)\s*\{)/s',
            '/(Route::middleware\s*\(\s*\[[\'"]auth[\'"]\s*\]\s*\)\s*->\s*group\s*\(\s*function\s*\(\)\s*\{)/s',
        ];

        $found = false;
        foreach ($middlewarePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $insertionPoint = strpos($content, $matches[1]) + strlen($matches[1]);
                $content = substr_replace($content, "\n    " . $routesContent . "\n", $insertionPoint, 0);
                File::put($routesPath, $content);
                $this->info('✅ Routes injected successfully.');
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->warn('⚠️  Could not find auth middleware group in routes/web.php');
            $this->line('   Please manually add the routes from vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/routes-to-add.php');
            $this->line('   The routes should be placed inside Route::middleware([\'auth\'])->group(...).');
        }
    }

    /**
     * Resolve sidebar blade path (primary: layouts/app, fallback: components/layouts/app).
     */
    private function getSidebarPath(): ?string
    {
        $primary = resource_path('views/layouts/app/sidebar.blade.php');
        $fallback = resource_path('views/components/layouts/app/sidebar.blade.php');

        return File::exists($primary) ? $primary : (File::exists($fallback) ? $fallback : null);
    }

    /**
     * Inject sidebar menu into sidebar.blade.php
     */
    private function injectSidebarMenu(): void
    {
        $sidebarPath = $this->getSidebarPath();
        $sidebarToAdd = base_path('vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.blade.php');

        // If developing locally, use local path
        if (!File::exists($sidebarToAdd)) {
            $sidebarToAdd = __DIR__ . '/../../stubs/reference/sidebar-menu-to-add.blade.php';
        }

        if ($sidebarPath === null) {
            $this->warn('⚠️  Sidebar file not found (checked layouts/app/sidebar.blade.php and components/layouts/app/sidebar.blade.php).');
            $this->line('   Please manually add the sidebar menu code.');
            return;
        }

        if (!File::exists($sidebarToAdd)) {
            $this->error('❌ sidebar-menu-to-add.blade.php not found.');
            return;
        }

        $content = File::get($sidebarPath);
        $sidebarContent = File::get($sidebarToAdd);

        // Extract just the menu code (remove PHP tags if present)
        // Remove PHP opening tag at the start
        $sidebarContent = preg_replace('/^<\?php\s*/', '', $sidebarContent);
        // Remove PHP closing tag (can appear anywhere)
        $sidebarContent = preg_replace('/\?>\s*/', '', $sidebarContent);
        // Remove single-line PHP comments (//) but keep Blade comments ({{-- --}})
        $sidebarContent = preg_replace('/\/\/.*$/m', '', $sidebarContent);
        $sidebarContent = trim($sidebarContent);

        // Check if menu already exists - check for multiple markers
        $menuMarkers = [
            'admin.countries.index',
            'admin.cities.index',
            'admin.availabilities.index',
            'admin.inheritance.index',
            'admin.admins.index',
            "route('admin.countries.index')",
            "route('admin.cities.index')",
            "{{ __('Countries') }}",
            "{{ __('Availability') }}",
        ];

        $menuExists = false;
        foreach ($menuMarkers as $marker) {
            if (strpos($content, $marker) !== false) {
                $menuExists = true;
                break;
            }
        }

        if ($menuExists) {
            $this->warn('⚠️  Sidebar menu already exists. Skipping sidebar injection.');
            return;
        }

        // Find Platform flux:sidebar.group - insert before closing tag
        $lines = explode("\n", $content);
        $insertionLine = -1;

        for ($i = 0; $i < count($lines); $i++) {
            // Match Platform flux:sidebar.group opening tag
            if (preg_match('/<flux:sidebar\.group\s+.*?:heading=["\']__\(["\']Platform["\']\)["\'].*?>/i', $lines[$i])) {
                // Find the closing </flux:sidebar.group> and insert before it
                for ($j = $i + 1; $j < min($i + 50, count($lines)); $j++) {
                    if (preg_match('/<\/flux:sidebar\.group>/i', $lines[$j])) {
                        $insertionLine = $j;
                        break 2;
                    }
                }
            }
        }

        // Fallback: more flexible pattern for Platform heading
        if ($insertionLine === -1) {
            for ($i = 0; $i < count($lines); $i++) {
                if (preg_match('/<flux:sidebar\.group.*?heading.*?Platform.*?>/i', $lines[$i])) {
                    for ($j = $i + 1; $j < min($i + 50, count($lines)); $j++) {
                        if (preg_match('/<\/flux:sidebar\.group>/i', $lines[$j])) {
                            $insertionLine = $j;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($insertionLine !== -1) {
            $sidebarLines = explode("\n", $sidebarContent);
            array_splice($lines, $insertionLine, 0, $sidebarLines);
            $content = implode("\n", $lines);
            File::put($sidebarPath, $content);
            $this->info('✅ Sidebar menu injected successfully.');
        } else {
            $this->warn('⚠️  Could not find Platform flux:sidebar.group in sidebar file.');
            $this->line('   Please manually add the menu code inside the Platform group in layouts/app/sidebar.blade.php or components/layouts/app/sidebar.blade.php.');
            $this->line('   See vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.blade.php for the content.');
        }
    }

    /**
     * Copy EnsureUserIsActive.php only. Does not modify FortifyServiceProvider.
     * See docs/FORTIFY-IS-ACTIVE.md for registering the action in the auth pipeline.
     */
    private function copyEnsureUserIsActiveOnly(): void
    {
        $stubBase = base_path('vendor/vormiaphp/ui-livewireflux-admin/src/stubs');
        if (!File::exists($stubBase)) {
            $stubBase = __DIR__ . '/../../stubs';
        }

        $ensureUserIsActiveStub = $stubBase . '/app/Actions/Fortify/EnsureUserIsActive.php';
        $ensureUserIsActiveDest = app_path('Actions/Fortify/EnsureUserIsActive.php');

        if (!File::exists($ensureUserIsActiveStub)) {
            $this->warn('⚠️  EnsureUserIsActive.php stub not found. Skipping.');
            return;
        }

        try {
            File::ensureDirectoryExists(dirname($ensureUserIsActiveDest));
            File::copy($ensureUserIsActiveStub, $ensureUserIsActiveDest);
            $this->info('✅ EnsureUserIsActive.php copied successfully.');
            $this->comment('   Add it to your Fortify auth pipeline — see docs/FORTIFY-IS-ACTIVE.md');
        } catch (\Exception $e) {
            $this->error('❌ Failed to copy EnsureUserIsActive.php: ' . $e->getMessage());
        }
    }

    /**
     * Clear application caches
     */
    private function clearCaches(): void
    {
        $cacheCommands = [
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'cache:clear' => 'Application cache',
        ];

        foreach ($cacheCommands as $command => $description) {
            try {
                \Illuminate\Support\Facades\Artisan::call($command);
                $this->line("  Cleared: {$description}");
            } catch (\Exception $e) {
                $this->line("  Skipped: {$description} (not available)");
            }
        }

        $this->info('✅ Caches cleared successfully.');
    }

    /**
     * Display completion message
     */
    private function displayCompletionMessage(): void
    {
        $this->newLine();
        $this->info('🎉 UI Livewire Flux Admin package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps:');
        $this->line('   1. Review routes/web.php to ensure admin routes were added');
        $this->line('   2. Review sidebar.blade.php to ensure menu items were added');
        $this->line('   3. To assign a role on registration: see docs/ROLE-ON-REGISTRATION.md');
        $this->line('   4. If using Fortify + EnsureUserIsActive: see docs/FORTIFY-IS-ACTIVE.md');
        $this->newLine();

        $this->comment('📖 For help and available commands: php artisan ui-livewireflux-admin:help');
        $this->newLine();

        $this->info('✨ Happy coding with UI Livewire Flux Admin!');
    }
}
