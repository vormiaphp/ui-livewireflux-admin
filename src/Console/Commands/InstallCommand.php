<?php

namespace Vormia\UILivewireFluxAdmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Vormia\UILivewireFluxAdmin\VormiaUiLivewireFlux;

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

        $vormia = new VormiaUiLivewireFlux();

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
        if (class_exists('Livewire\Flux\Flux')) {
            $this->step('Injecting sidebar menu...');
            $this->injectSidebarMenu();
        } else {
            $this->warn('⚠️  livewire/flux is not installed. Sidebar menu will not be automatically injected.');
            $this->line('   You will need to manually add the navigation links to resources/views/components/layouts/app/sidebar.php');
        }

        // Step 4: Update CreateNewUser (if laravel/fortify exists)
        if (class_exists('Laravel\Fortify\Fortify')) {
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
            'vormiaphp/vormia' => ['VormiaPHP\Vormia\VormiaServiceProvider', 'Vormia\Vormia\VormiaServiceProvider'],
            'livewire/volt' => ['Livewire\Volt\Volt'],
        ];

        $allGood = true;
        foreach ($required as $package => $classes) {
            $found = false;
            foreach ((array)$classes as $class) {
                if (class_exists($class)) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
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
        $routesToAdd = base_path('vendor/vormiaphp/ui-livewireflux-admin/routes-to-add.php');
        
        // If developing locally, use local path
        if (!File::exists($routesToAdd)) {
            $routesToAdd = __DIR__ . '/../../../routes-to-add.php';
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

        // Check if routes already exist
        if (strpos($content, 'admin.categories.index') !== false) {
            $this->warn('⚠️  Routes already exist in routes/web.php. Skipping route injection.');
            return;
        }

        // Find the middleware group and inject routes
        if (preg_match('/(Route::middleware\(\[[\'"]auth[\'"],\s*[\'"]authority[\'"]\]\)->group\(function\s*\(\)\s*\{)/s', $content, $matches)) {
            $insertionPoint = strpos($content, $matches[1]) + strlen($matches[1]);
            $content = substr_replace($content, "\n    " . $routesContent . "\n", $insertionPoint, 0);
            File::put($routesPath, $content);
            $this->info('✅ Routes injected successfully.');
        } else {
            $this->warn('⚠️  Could not find Route::middleware([\'auth\', \'authority\'])->group in routes/web.php');
            $this->line('   Please manually add the routes from routes-to-add.php');
        }
    }

    /**
     * Inject sidebar menu into sidebar.blade.php
     */
    private function injectSidebarMenu(): void
    {
        $sidebarPath = resource_path('views/components/layouts/app/sidebar.php');
        $sidebarToAdd = base_path('vendor/vormiaphp/ui-livewireflux-admin/sidebar-menu-to-add.php');
        
        // If developing locally, use local path
        if (!File::exists($sidebarToAdd)) {
            $sidebarToAdd = __DIR__ . '/../../../sidebar-menu-to-add.php';
        }

        if (!File::exists($sidebarPath)) {
            $this->warn('⚠️  Sidebar file not found at: ' . $sidebarPath);
            $this->line('   Please manually add the sidebar menu code.');
            return;
        }

        if (!File::exists($sidebarToAdd)) {
            $this->error('❌ sidebar-menu-to-add.php not found.');
            return;
        }

        $content = File::get($sidebarPath);
        $sidebarContent = File::get($sidebarToAdd);
        
        // Extract just the menu code (remove PHP tags and comments)
        $sidebarContent = preg_replace('/^<\?php\s*/', '', $sidebarContent);
        $sidebarContent = preg_replace('/\/\/.*$/m', '', $sidebarContent);
        $sidebarContent = trim($sidebarContent);

        // Check if menu already exists
        if (strpos($content, 'admin.countries.index') !== false) {
            $this->warn('⚠️  Sidebar menu already exists. Skipping sidebar injection.');
            return;
        }

        // Find line 20 (after Dashboard menu item) and inject
        $lines = explode("\n", $content);
        if (count($lines) >= 20) {
            // Insert after line 20 (index 19)
            array_splice($lines, 20, 0, explode("\n", $sidebarContent));
            $content = implode("\n", $lines);
            File::put($sidebarPath, $content);
            $this->info('✅ Sidebar menu injected successfully.');
        } else {
            $this->warn('⚠️  Could not find insertion point in sidebar file.');
            $this->line('   Please manually add the sidebar menu code after the Dashboard menu item.');
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

        // Copy stub
        File::copy($stubPath, $createNewUserPath);
        $this->info('✅ CreateNewUser updated successfully.');
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
        $this->line('   2. Review your sidebar.blade.php to ensure menu items were added');
        $this->line('   3. Test your admin routes');
        $this->newLine();

        $this->comment('📖 For help and available commands, run: php artisan ui-livewireflux-admin:help');
        $this->newLine();

        $this->info('✨ Happy coding with UI Livewire Flux Admin!');
    }
}

