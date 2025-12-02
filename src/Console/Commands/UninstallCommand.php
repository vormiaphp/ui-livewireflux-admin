<?php

namespace Vormia\UILivewireFluxAdmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Vormia\UILivewireFluxAdmin\UILivewireFlux;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui-livewireflux-admin:uninstall {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all UI Livewire Flux Admin package files and configurations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗑️  Uninstalling UI Livewire Flux Admin Package...');
        $this->newLine();

        $force = $this->option('force');

        // Warning message
        $this->error('⚠️  DANGER: This will completely remove UI Livewire Flux Admin from your application!');
        $this->warn('   This action will:');
        $this->warn('   • Remove all package files and directories');
        $this->warn('   • Remove routes from routes/web.php');
        $this->warn('   • Remove sidebar menu code');
        $this->warn('   • Revert CreateNewUser changes');
        $this->newLine();

        if (!$force && !$this->confirm('Are you absolutely sure you want to uninstall?', false)) {
            $this->info('❌ Uninstall cancelled.');
            return;
        }

        // Final confirmation
        if (!$force) {
            $this->newLine();
            $this->error('🚨 FINAL WARNING: This action cannot be undone!');
            if (!$this->confirm('Type "yes" to proceed with uninstallation', false)) {
                $this->info('❌ Uninstall cancelled.');
                return;
            }
        }

        // Step 1: Create final backup
        $this->step('Creating final backup...');
        $this->createFinalBackup();

        // Step 2: Remove files
        $this->step('Removing package files...');
        $vormia = new UILivewireFlux();
        if ($vormia->uninstall()) {
            $this->info('✅ Files removed successfully.');
        } else {
            $this->error('❌ Failed to remove files.');
            return 1;
        }

        // Step 3: Remove routes
        $this->step('Removing routes from routes/web.php...');
        $this->removeRoutes();

        // Step 4: Remove sidebar menu
        $this->step('Removing sidebar menu...');
        $this->removeSidebarMenu();

        // Step 5: Revert CreateNewUser
        $this->step('Reverting CreateNewUser changes...');
        $this->revertCreateNewUser();

        // Step 6: Clear caches
        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->displayCompletionMessage();
    }

    /**
     * Display a step message
     */
    private function step($message)
    {
        $this->info("🗂️  {$message}");
    }

    /**
     * Create final backup before uninstallation
     */
    private function createFinalBackup()
    {
        $backupDir = storage_path('app/ui-livewireflux-admin-final-backup-' . date('Y-m-d-H-i-s'));

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filesToBackup = [
            app_path('View/Components/AdminPanel.php') => $backupDir . '/View/Components/AdminPanel.php',
            app_path('Actions/Fortify/CreateNewUser.php') => $backupDir . '/Actions/Fortify/CreateNewUser.php',
            resource_path('views/components/admin-panel.blade.php') => $backupDir . '/views/components/admin-panel.blade.php',
            resource_path('views/livewire/admin') => $backupDir . '/views/livewire/admin',
            base_path('routes/web.php') => $backupDir . '/routes/web.php',
            resource_path('views/components/layouts/app/sidebar.php') => $backupDir . '/views/components/layouts/app/sidebar.php',
        ];

        foreach ($filesToBackup as $source => $destination) {
            if (File::exists($source)) {
                if (File::isDirectory($source)) {
                    File::copyDirectory($source, $destination);
                } else {
                    File::ensureDirectoryExists(dirname($destination));
                    File::copy($source, $destination);
                }
            }
        }

        $this->info("✅ Final backup created in: {$backupDir}");
    }

    /**
     * Remove routes from routes/web.php
     */
    private function removeRoutes(): void
    {
        $routesPath = base_path('routes/web.php');

        if (!File::exists($routesPath)) {
            $this->warn('⚠️  routes/web.php not found.');
            return;
        }

        $content = File::get($routesPath);

        // Remove the admin routes group
        $pattern = '/Route::group\(\[\'prefix\'\s*=>\s*\'admin\'\],\s*function\s*\(\)\s*\{[^}]*Volt::route\([^}]*\};\s*\}\);/s';
        $content = preg_replace($pattern, '', $content);

        // Clean up extra whitespace
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

        File::put($routesPath, $content);
        $this->info('✅ Routes removed successfully.');
    }

    /**
     * Remove sidebar menu code
     */
    private function removeSidebarMenu(): void
    {
        $sidebarPath = resource_path('views/components/layouts/app/sidebar.php');

        if (!File::exists($sidebarPath)) {
            $this->warn('⚠️  Sidebar file not found.');
            return;
        }

        $content = File::get($sidebarPath);

        // Remove the admin menu section
        $pattern = '/<span class="h-px w-full bg-zinc-200 dark:bg-zinc-700"><\/span>.*?@endif\s*@if \(auth\(\)->user\(\)\?->isSuperAdmin\(\)\).*?@endif/s';
        $content = preg_replace($pattern, '', $content);

        // Clean up extra whitespace
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

        File::put($sidebarPath, $content);
        $this->info('✅ Sidebar menu removed successfully.');
    }

    /**
     * Revert CreateNewUser changes
     */
    private function revertCreateNewUser(): void
    {
        $createNewUserPath = app_path('Actions/Fortify/CreateNewUser.php');

        if (!File::exists($createNewUserPath)) {
            $this->warn('⚠️  CreateNewUser.php not found.');
            return;
        }

        $content = File::get($createNewUserPath);

        // Remove role attachment line
        $content = preg_replace('/\s*\/\/ Attach to role\s*\n\s*\$user->roles\(\)->attach\(1\);.*?\n/', '', $content);

        File::put($createNewUserPath, $content);
        $this->info('✅ CreateNewUser reverted successfully.');
    }

    /**
     * Clear application caches
     */
    private function clearCaches()
    {
        $cacheCommands = [
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'cache:clear' => 'Application cache',
        ];

        foreach ($cacheCommands as $command => $description) {
            try {
                Artisan::call($command);
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
        $this->info('🎉 UI Livewire Flux Admin package uninstalled successfully!');
        $this->newLine();

        $this->comment('📋 What was removed:');
        $this->line('   ✅ All package files and directories');
        $this->line('   ✅ Routes from routes/web.php');
        $this->line('   ✅ Sidebar menu code');
        $this->line('   ✅ CreateNewUser role attachment');
        $this->line('   ✅ Application caches cleared');
        $this->line('   ✅ Final backup created in storage/app/');
        $this->newLine();

        $this->comment('📖 Final steps:');
        $this->line('   1. Remove "vormiaphp/ui-livewireflux-admin" from your composer.json');
        $this->line('   2. Run: composer remove vormiaphp/ui-livewireflux-admin');
        $this->line('   3. Review your routes/web.php and sidebar.blade.php for any remaining code');
        $this->newLine();

        $this->info('✨ Thank you for using UI Livewire Flux Admin!');
    }
}

