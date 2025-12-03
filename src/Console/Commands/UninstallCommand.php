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
            resource_path('views/components/layouts/app/sidebar.blade.php') => $backupDir . '/views/components/layouts/app/sidebar.blade.php',
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
     * Remove routes from routes/web.php by exact line matching
     */
    private function removeRoutes(): void
    {
        $routesPath = base_path('routes/web.php');

        if (!File::exists($routesPath)) {
            $this->warn('⚠️  routes/web.php not found.');
            return;
        }

        $content = File::get($routesPath);
        $lines = explode("\n", $content);

        // Define exact route patterns to match (with flexible whitespace)
        $routePatterns = [
            // Categories
            "/\s*Volt::route\s*\(\s*['\"]categories['\"]\s*,\s*['\"]admin\.control\.categories\.index['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.categories\.index['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]categories\/create['\"]\s*,\s*['\"]admin\.control\.categories\.create['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.categories\.create['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]categories\/edit\/\{id\}['\"]\s*,\s*['\"]admin\.control\.categories\.edit['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.categories\.edit['\"]\s*\)\s*;/",
            
            // Inheritance
            "/\s*Volt::route\s*\(\s*['\"]inheritance['\"]\s*,\s*['\"]admin\.control\.inheritance\.index['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.inheritance\.index['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]inheritance\/create['\"]\s*,\s*['\"]admin\.control\.inheritance\.create['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.inheritance\.create['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]inheritance\/edit\/\{id\}['\"]\s*,\s*['\"]admin\.control\.inheritance\.edit['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.inheritance\.edit['\"]\s*\)\s*;/",
            
            // Countries
            "/\s*Volt::route\s*\(\s*['\"]countries['\"]\s*,\s*['\"]admin\.control\.locations\.index['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.countries\.index['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]countries\/create['\"]\s*,\s*['\"]admin\.control\.locations\.create['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.countries\.create['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]countries\/edit\/\{id\}['\"]\s*,\s*['\"]admin\.control\.locations\.edit['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.countries\.edit['\"]\s*\)\s*;/",
            
            // Cities
            "/\s*Volt::route\s*\(\s*['\"]cities['\"]\s*,\s*['\"]admin\.control\.locations\.index['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.cities\.index['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]cities\/create['\"]\s*,\s*['\"]admin\.control\.locations\.create['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.cities\.create['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]cities\/edit\/\{id\}['\"]\s*,\s*['\"]admin\.control\.locations\.edit['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.cities\.edit['\"]\s*\)\s*;/",
            
            // Availabilities
            "/\s*Volt::route\s*\(\s*['\"]availabilities['\"]\s*,\s*['\"]admin\.control\.availability\.index['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.availabilities\.index['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]availabilities\/create['\"]\s*,\s*['\"]admin\.control\.availability\.create['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.availabilities\.create['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]availabilities\/edit\/\{id\}['\"]\s*,\s*['\"]admin\.control\.availability\.edit['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.availabilities\.edit['\"]\s*\)\s*;/",
            
            // Admins
            "/\s*Volt::route\s*\(\s*['\"]admins['\"]\s*,\s*['\"]admin\.admins\.index['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.admins\.index['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]admins\/create['\"]\s*,\s*['\"]admin\.admins\.create['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.admins\.create['\"]\s*\)\s*;/",
            "/\s*Volt::route\s*\(\s*['\"]admins\/edit\/\{id\}['\"]\s*,\s*['\"]admin\.admins\.edit['\"]\s*\)\s*->name\s*\(\s*['\"]admin\.admins\.edit['\"]\s*\)\s*;/",
        ];

        // Also match comment lines that might be associated with these routes
        $commentPatterns = [
            "/\s*\/\/\s*Categories/",
            "/\s*\/\/\s*Inheritance/",
            "/\s*\/\/\s*Locations\s*-\s*Countries/",
            "/\s*\/\/\s*Locations\s*-\s*Cities/",
            "/\s*\/\/\s*Availability\s*taxonomy/",
            "/\s*\/\/\s*Admins/",
        ];

        $removedCount = 0;
        $newLines = [];

        foreach ($lines as $line) {
            $shouldRemove = false;

            // Check if line matches any route pattern
            foreach ($routePatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $shouldRemove = true;
                    $removedCount++;
                    break;
                }
            }

            // Check if line matches comment patterns (only if it's a comment line)
            if (!$shouldRemove && preg_match('/^\s*\/\//', $line)) {
                foreach ($commentPatterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $shouldRemove = true;
                        break;
                    }
                }
            }

            if (!$shouldRemove) {
                $newLines[] = $line;
            }
        }

        // Remove empty Route::group blocks that might be left behind
        $content = implode("\n", $newLines);
        
        // Remove empty Route::group(['prefix' => 'admin'], function () { }); blocks
        $content = preg_replace('/Route::group\s*\(\s*\[\s*[\'"]prefix[\'"]\s*=>\s*[\'"]admin[\'"]\s*\]\s*,\s*function\s*\(\s*\)\s*\{\s*\}\s*\)\s*;/s', '', $content);
        
        // Remove Route::group opening with only whitespace/comments before closing
        $content = preg_replace('/Route::group\s*\(\s*\[\s*[\'"]prefix[\'"]\s*=>\s*[\'"]admin[\'"]\s*\]\s*,\s*function\s*\(\s*\)\s*\{\s*(?:\/\/.*?\n\s*)*\}\s*\)\s*;/s', '', $content);

        // Clean up extra whitespace
        $content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);

        File::put($routesPath, $content);
        
        if ($removedCount > 0) {
            $this->info("✅ Removed {$removedCount} route(s) successfully.");
        } else {
            $this->warn('⚠️  No matching routes found to remove.');
        }
    }

    /**
     * Remove sidebar menu code by reading exact patterns from stub file
     */
    private function removeSidebarMenu(): void
    {
        $sidebarPath = resource_path('views/components/layouts/app/sidebar.blade.php');
        $sidebarStubPath = base_path('vendor/vormiaphp/ui-livewireflux-admin/src/stubs/reference/sidebar-menu-to-add.blade.php');

        // If developing locally, use local path
        if (!File::exists($sidebarStubPath)) {
            $sidebarStubPath = __DIR__ . '/../../stubs/reference/sidebar-menu-to-add.blade.php';
        }

        if (!File::exists($sidebarPath)) {
            $this->warn('⚠️  Sidebar file not found.');
            return;
        }

        if (!File::exists($sidebarStubPath)) {
            $this->warn('⚠️  sidebar-menu-to-add.blade.php stub not found.');
            return;
        }

        // Read the stub file to get exact patterns
        $stubContent = File::get($sidebarStubPath);
        $stubLines = explode("\n", $stubContent);

        // Extract the actual menu content (skip comments)
        $menuLines = [];
        foreach ($stubLines as $line) {
            // Skip Blade comments and empty lines at the start
            if (preg_match('/^\s*\{\{--.*--\}\}\s*$/', $line) || 
                (empty($menuLines) && trim($line) === '')) {
                continue;
            }
            $menuLines[] = $line;
        }

        // Read the sidebar file
        $content = File::get($sidebarPath);
        $lines = explode("\n", $content);

        // Track which lines to remove
        $linesToRemove = [];
        $inAdminBlock = false;
        $inSuperAdminBlock = false;

        // First pass: identify all lines to remove
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmedLine = trim($line);

            // Check for @if (auth()->user()?->isAdminOrSuperAdmin())
            if (preg_match('/@if\s*\(\s*auth\(\)\s*->\s*user\(\)\s*\?->\s*isAdminOrSuperAdmin\(\)\s*\)/', $line)) {
                $linesToRemove[$i] = true;
                $inAdminBlock = true;
            }
            // Check for @endif (closing the admin block)
            elseif ($inAdminBlock && preg_match('/@endif/', $line)) {
                $linesToRemove[$i] = true;
                $inAdminBlock = false;
            }
            // Check for @if (auth()->user()?->isSuperAdmin())
            elseif (preg_match('/@if\s*\(\s*auth\(\)\s*->\s*user\(\)\s*\?->\s*isSuperAdmin\(\)\s*\)/', $line)) {
                $linesToRemove[$i] = true;
                $inSuperAdminBlock = true;
            }
            // Check for @endif (closing the super admin block)
            elseif ($inSuperAdminBlock && preg_match('/@endif/', $line)) {
                $linesToRemove[$i] = true;
                $inSuperAdminBlock = false;
            }
            // Check for HR tags within blocks
            elseif (($inAdminBlock || $inSuperAdminBlock) && preg_match('/<hr\s*\/?>/', $trimmedLine)) {
                $linesToRemove[$i] = true;
            }
            // Check for Categories menu item (lines 7-11 in stub)
            elseif ($inAdminBlock && preg_match('/<flux:navlist\.item\s+icon=["\']tag["\'].*?route\(["\']admin\.categories\.index["\']\)/s', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing tag
                for ($j = $i + 1; $j < min($i + 5, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.item>/', $lines[$j])) {
                        break;
                    }
                }
            }
            // Check for Countries menu item (lines 12-16 in stub)
            elseif ($inAdminBlock && preg_match('/<flux:navlist\.item\s+icon=["\']map-pin["\'].*?route\(["\']admin\.countries\.index["\']\)/s', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing tag
                for ($j = $i + 1; $j < min($i + 5, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.item>/', $lines[$j])) {
                        break;
                }
                }
            }
            // Check for Cities menu item (lines 17-21 in stub)
            elseif ($inAdminBlock && preg_match('/<flux:navlist\.item\s+icon=["\']building-office["\'].*?route\(["\']admin\.cities\.index["\']\)/s', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing tag
                for ($j = $i + 1; $j < min($i + 5, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.item>/', $lines[$j])) {
                        break;
                }
                }
            }
            // Check for Availability menu item (lines 23-27 in stub)
            elseif ($inAdminBlock && preg_match('/<flux:navlist\.item\s+icon=["\']check-circle["\'].*?route\(["\']admin\.availabilities\.index["\']\)/s', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing tag
                for ($j = $i + 1; $j < min($i + 5, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.item>/', $lines[$j])) {
                        break;
                }
                }
            }
            // Check for Inheritance menu item (lines 28-32 in stub)
            elseif ($inAdminBlock && preg_match('/<flux:navlist\.item\s+icon=["\']folder-git-2["\'].*?route\(["\']admin\.inheritance\.index["\']\)/s', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing tag
                for ($j = $i + 1; $j < min($i + 5, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.item>/', $lines[$j])) {
                        break;
                }
                }
            }
            // Check for Admins group (lines 35-42 in stub)
            elseif ($inSuperAdminBlock && preg_match('/<flux:navlist\.group\s+:heading=["\']__\(["\']Admin["\']\)["\'].*?class=["\']grid["\']>/', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing group tag
                for ($j = $i + 1; $j < min($i + 10, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.group>/', $lines[$j])) {
                        break;
                    }
                }
            }
            // Check for Admins menu item within the group
            elseif ($inSuperAdminBlock && preg_match('/<flux:navlist\.item\s+icon=["\']shield-check["\'].*?route\(["\']admin\.admins\.index["\']\)/s', $line)) {
                $linesToRemove[$i] = true;
                // Continue removing until closing tag
                for ($j = $i + 1; $j < min($i + 5, count($lines)); $j++) {
                    $linesToRemove[$j] = true;
                    if (preg_match('/<\/flux:navlist\.item>/', $lines[$j])) {
                        break;
                    }
                }
            }
            // Check for content lines within menu items (Categories, Countries, Cities, Availability, Inheritance, Admins text)
            elseif (($inAdminBlock || $inSuperAdminBlock) && (
                preg_match('/\{\{\s*__\(["\']Categories["\']\)\s*\}\}/', $trimmedLine) ||
                preg_match('/\{\{\s*__\(["\']Countries["\']\)\s*\}\}/', $trimmedLine) ||
                preg_match('/\{\{\s*__\(["\']Cities["\']\)\s*\}\}/', $trimmedLine) ||
                preg_match('/\{\{\s*__\(["\']Availability["\']\)\s*\}\}/', $trimmedLine) ||
                preg_match('/\{\{\s*__\(["\']Inheritance["\']\)\s*\}\}/', $trimmedLine) ||
                preg_match('/\{\{\s*__\(["\']Admins["\']\)\s*\}\}/', $trimmedLine)
            )) {
                $linesToRemove[$i] = true;
            }
        }

        // Second pass: build new content without removed lines
        $newLines = [];
        $removedCount = 0;
        for ($i = 0; $i < count($lines); $i++) {
            if (!isset($linesToRemove[$i])) {
                $newLines[] = $lines[$i];
            } else {
                $removedCount++;
            }
        }

        // Clean up extra whitespace
        $content = implode("\n", $newLines);
        $content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);

        File::put($sidebarPath, $content);
        
        if ($removedCount > 0) {
            $this->info("✅ Removed {$removedCount} sidebar menu item(s) successfully.");
        } else {
            $this->warn('⚠️  No matching sidebar menu items found to remove.');
        }
    }

    /**
     * Revert CreateNewUser changes
     * Restores from CreateNewUser.backup file by copying it and renaming to .php
     */
    private function revertCreateNewUser(): void
    {
        $createNewUserPath = app_path('Actions/Fortify/CreateNewUser.php');
        $backupPath = $this->getCreateNewUserBackupPath();

        // Step 1: Check for backup file (.backup extension)
        if (!File::exists($backupPath)) {
            $this->warn('⚠️  Original CreateNewUser.backup not found at: ' . $backupPath);
            $this->warn('⚠️  Cannot restore original file. The installed stub will be deleted, but original cannot be restored.');
            $this->line('   You may need to manually restore CreateNewUser.php from your version control.');
            
            // Still delete the installed stub if it exists
            if (File::exists($createNewUserPath)) {
                try {
                    File::delete($createNewUserPath);
                    $this->line('  Installed CreateNewUser.php stub deleted.');
                } catch (\Exception $e) {
                    $this->error('❌ Failed to delete CreateNewUser.php: ' . $e->getMessage());
                }
            }
            return;
        }

        // Step 2: Delete the installed stub file
        if (File::exists($createNewUserPath)) {
            try {
                File::delete($createNewUserPath);
                $this->line('  Installed CreateNewUser.php stub deleted.');
            } catch (\Exception $e) {
                $this->error('❌ Failed to delete installed CreateNewUser.php: ' . $e->getMessage());
                $this->warn('⚠️  Cannot proceed with restore while installed file exists.');
                return;
            }
        } else {
            $this->warn('⚠️  CreateNewUser.php not found. It may have already been deleted.');
        }

        // Step 3: Restore original from .backup file (copy and rename to .php)
        try {
            File::ensureDirectoryExists(dirname($createNewUserPath));
            File::copy($backupPath, $createNewUserPath);
            $this->info('✅ CreateNewUser restored from CreateNewUser.backup successfully.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to restore CreateNewUser.php from backup: ' . $e->getMessage());
            $this->warn('⚠️  Backup exists at: ' . $backupPath);
            $this->line('   You may need to manually copy CreateNewUser.backup to CreateNewUser.php');
            return;
        }

        // Step 4: Delete the backup file after successful restoration
        try {
            if (File::exists($backupPath)) {
                File::delete($backupPath);
                $this->line('  CreateNewUser.backup file deleted.');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Failed to delete CreateNewUser.backup: ' . $e->getMessage());
            $this->line('   You may need to manually delete: ' . $backupPath);
        }
    }

    /**
     * Get the backup path for CreateNewUser.php
     * Looks for the .backup file in the same directory as the original
     */
    private function getCreateNewUserBackupPath(): string
    {
        return app_path('Actions/Fortify/CreateNewUser.backup');
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

