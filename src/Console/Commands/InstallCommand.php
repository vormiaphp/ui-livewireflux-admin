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
            $this->line('   You will need to manually add the navigation links to resources/views/components/layouts/app/sidebar.blade.php');
        }

        // Step 4: Update CreateNewUser (if laravel/fortify exists)
        if (InstalledVersions::isInstalled('laravel/fortify')) {
            $this->step('Updating CreateNewUser action...');
            $this->updateCreateNewUser();
        } else {
            $this->warn('⚠️  laravel/fortify is not installed. CreateNewUser will not be automatically updated.');
            $this->line('   You will need to manually attach the admin role (ID: 1) to new users.');
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

        // Find the middleware group - try multiple patterns
        $middlewarePatterns = [
            // Standard pattern
            '/(Route::middleware\(\[[\'"]auth[\'"]\]\)->group\(function\s*\(\)\s*\{)/s',
            // With spaces variations
            '/(Route::middleware\s*\(\s*\[[\'"]auth[\'"]\s*\]\s*\)\s*->\s*group\s*\(\s*function\s*\(\)\s*\{)/s',
            // Single quotes
            '/(Route::middleware\(\[\'auth\'\]\)->group\(function\s*\(\)\s*\{)/s',
        ];

        $found = false;
        foreach ($middlewarePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $insertionPoint = strpos($content, $matches[1]) + strlen($matches[1]);
                $content = substr_replace($content, "\n    " . $routesContent . "\n", $insertionPoint, 0);
                File::put($routesPath, $content);
                $this->info('✅ Routes injected successfully.');
                $this->comment('   Note: If you have configured your own starterkit, you may need to add:');
                $this->line('   use Livewire\Volt\Volt;');
                $this->line('   at the top of your routes/web.php file.');
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->warn('⚠️  Could not find Route::middleware([\'auth\'])->group in routes/web.php');
            $this->line('   Please manually add the routes from vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/routes-to-add.php');
            $this->line('   The routes should be placed inside the middleware group.');
            $this->newLine();
            $this->comment('   Note: If you have configured your own starterkit, you may need to add:');
            $this->line('   use Livewire\Volt\Volt;');
            $this->line('   at the top of your routes/web.php file.');
        }
    }

    /**
     * Inject sidebar menu into sidebar.blade.php
     */
    private function injectSidebarMenu(): void
    {
        $sidebarPath = resource_path('views/components/layouts/app/sidebar.blade.php');
        $sidebarToAdd = base_path('vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.blade.php');

        // If developing locally, use local path
        if (!File::exists($sidebarToAdd)) {
            $sidebarToAdd = __DIR__ . '/../../stubs/reference/sidebar-menu-to-add.blade.php';
        }

        if (!File::exists($sidebarPath)) {
            $this->warn('⚠️  Sidebar file not found at: ' . $sidebarPath);
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

        // Find Platform navlist.group - look for the closing tag
        $lines = explode("\n", $content);
        $insertionLine = -1;

        // Pattern to find Platform group closing tag
        // Look for </flux:navlist.group> after finding Platform heading
        $inPlatformGroup = false;
        for ($i = 0; $i < count($lines); $i++) {
            // Check if this line contains the Platform group opening tag
            if (preg_match('/<flux:navlist\.group\s+.*?:heading=["\']__\(["\']Platform["\']\)["\'].*?class=["\']grid["\']>/i', $lines[$i])) {
                $inPlatformGroup = true;
                continue;
            }

            // If we're in the Platform group, look for the closing tag
            if ($inPlatformGroup && preg_match('/<\/flux:navlist\.group>/i', $lines[$i])) {
                $insertionLine = $i + 1;
                break;
            }
        }

        // Fallback: if Platform group not found, try to find it with more flexible patterns
        if ($insertionLine === -1) {
            // Try to find Platform heading with more flexible whitespace
            for ($i = 0; $i < count($lines); $i++) {
                // Match Platform heading with various whitespace patterns
                if (preg_match('/<flux:navlist\.group.*?heading.*?Platform.*?>/i', $lines[$i])) {
                    // Find the closing tag within reasonable distance (next 20 lines)
                    for ($j = $i + 1; $j < min($i + 20, count($lines)); $j++) {
                        if (preg_match('/<\/flux:navlist\.group>/i', $lines[$j])) {
                            $insertionLine = $j + 1;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($insertionLine !== -1 && $insertionLine <= count($lines)) {
            // Insert the sidebar content
            $sidebarLines = explode("\n", $sidebarContent);
            array_splice($lines, $insertionLine, 0, $sidebarLines);
            $content = implode("\n", $lines);
            File::put($sidebarPath, $content);
            $this->info('✅ Sidebar menu injected successfully.');
        } else {
            $this->warn('⚠️  Could not find Platform navlist.group in sidebar file.');
            $this->line('   Please manually add the sidebar menu code after the Platform group closing tag.');
            $this->line('   The menu code should be placed in: ' . $sidebarPath);
        }
    }

    /**
     * Update CreateNewUser action
     */
    private function updateCreateNewUser(): void
    {
        $createNewUserPath = app_path('Actions/Fortify/CreateNewUser.php');
        $stubPath = base_path('vendor/vormiaphp/ui-livewireflux-admin/src/stubs/app/Actions/Fortify/CreateNewUser.php');

        // If developing locally, use local path
        if (!File::exists($stubPath)) {
            $stubPath = __DIR__ . '/../../stubs/app/Actions/Fortify/CreateNewUser.php';
        }

        if (!File::exists($createNewUserPath)) {
            $this->warn('⚠️  CreateNewUser.php not found. Skipping update.');
            return;
        }

        if (!File::exists($stubPath)) {
            $this->warn('⚠️  CreateNewUser stub not found. Skipping update.');
            return;
        }

        // Check if already updated
        $content = File::get($createNewUserPath);
        if (strpos($content, 'roles()->attach(1)') !== false) {
            $this->info('✅ CreateNewUser already updated.');
            return;
        }

        // Backup original file before replacing it
        $backupPath = $this->getCreateNewUserBackupPath();

        // Only backup if backup doesn't exist yet (to preserve the true original)
        if (!File::exists($backupPath)) {
            File::ensureDirectoryExists(dirname($backupPath));
            File::copy($createNewUserPath, $backupPath);
            $this->line('  Original CreateNewUser.php backed up.');
        }

        // Copy stub
        File::copy($stubPath, $createNewUserPath);
        $this->info('✅ CreateNewUser updated successfully.');
    }

    /**
     * Get the backup path for CreateNewUser.php
     */
    private function getCreateNewUserBackupPath(): string
    {
        return storage_path('app/ui-livewireflux-admin-original/CreateNewUser.php');
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
    private function displayCompletionMessage()
    {
        $this->newLine();
        $this->info('🎉 UI Livewire Flux Admin package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps:');
        $this->line('   1. Review your routes/web.php to ensure routes were added correctly');
        $this->line('   2. If you have configured your own starterkit, add: use Livewire\Volt\Volt; at the top of routes/web.php');
        $this->line('   3. Review your sidebar.blade.php to ensure menu items were added');
        $this->line('   4. Test your admin routes');
        $this->newLine();

        $this->comment('📖 For help and available commands, run: php artisan ui-livewireflux-admin:help');
        $this->newLine();

        $this->info('✨ Happy coding with UI Livewire Flux Admin!');
    }
}
