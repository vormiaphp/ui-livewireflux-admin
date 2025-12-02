<?php

namespace Vormia\UILivewireFluxAdmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Vormia\UILivewireFluxAdmin\VormiaUiLivewireFlux;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui-livewireflux-admin:update {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update UI Livewire Flux Admin package files (removes old files and copies fresh ones)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating UI Livewire Flux Admin Package...');
        $this->newLine();

        $force = $this->option('force');

        // Warning message
        $this->warn('⚠️  WARNING: This will replace existing package files with fresh copies.');
        $this->warn('   Make sure you have backed up any custom modifications.');
        $this->newLine();

        if (!$force && !$this->confirm('Do you want to continue with the update?', false)) {
            $this->info('❌ Update cancelled.');
            return;
        }

        // Step 1: Create backup
        $this->step('Creating backup of existing files...');
        $this->createBackup();

        // Step 2: Update files
        $this->step('Updating package files...');
        $vormia = new VormiaUiLivewireFlux();
        if ($vormia->update()) {
            $this->info('✅ Files updated successfully.');
        } else {
            $this->error('❌ Failed to update files.');
            return 1;
        }

        // Step 3: Clear caches
        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->displayCompletionMessage();
    }

    /**
     * Display a step message
     */
    private function step($message)
    {
        $this->info("📦 {$message}");
    }

    /**
     * Create backup of existing files
     */
    private function createBackup()
    {
        $backupDir = storage_path('app/ui-livewireflux-admin-backups/' . date('Y-m-d-H-i-s'));

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filesToBackup = [
            app_path('View/Components/AdminPanel.php') => $backupDir . '/View/Components/AdminPanel.php',
            app_path('Actions/Fortify/CreateNewUser.php') => $backupDir . '/Actions/Fortify/CreateNewUser.php',
            resource_path('views/components/admin-panel.php') => $backupDir . '/views/components/admin-panel.php',
            resource_path('views/livewire/admin') => $backupDir . '/views/livewire/admin',
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

        $this->info("✅ Backup created in: {$backupDir}");
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
        $this->info('🎉 UI Livewire Flux Admin package updated successfully!');
        $this->newLine();

        $this->comment('📋 What was updated:');
        $this->line('   ✅ All package files replaced with fresh copies');
        $this->line('   ✅ Backups created in storage/app/ui-livewireflux-admin-backups/');
        $this->line('   ✅ Application caches cleared');
        $this->newLine();

        $this->comment('📖 Next steps:');
        $this->line('   1. Review any custom modifications in your backup files');
        $this->line('   2. Test your application to ensure everything works correctly');
        $this->newLine();

        $this->info('✨ Your UI Livewire Flux Admin package is now up to date!');
    }
}

