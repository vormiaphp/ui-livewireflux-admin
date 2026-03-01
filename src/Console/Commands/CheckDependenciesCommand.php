<?php

namespace Vormia\UILivewireFluxAdmin\Console\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;

class CheckDependenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui-livewireflux-admin:check-dependencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all required dependencies for UI Livewire Flux Admin are installed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking UI Livewire Flux Admin dependencies...');
        $this->newLine();

        $required = [
            'vormiaphp/vormia',
            'livewire/volt',
        ];

        $optional = [
            'livewire/flux',
            'laravel/fortify',
        ];

        $allGood = true;

        // Check required packages
        $this->info('Required packages:');
        foreach ($required as $package) {
            if (InstalledVersions::isInstalled($package)) {
                $this->line("  ✓ {$package}");
            } else {
                $this->error("  ✗ {$package} - MISSING");
                $this->line("    Please install it first: composer require {$package}");
                $allGood = false;
            }
        }

        $this->newLine();

        // Check optional packages
        $this->info('Optional packages:');
        foreach ($optional as $package) {
            if (InstalledVersions::isInstalled($package)) {
                $this->line("  ✓ {$package}");
            } else {
                $this->warn("  ⚠ {$package} - NOT INSTALLED");
                $this->displayOptionalPackageInfo($package);
            }
        }

        $this->newLine();

        if ($allGood) {
            $this->info('✅ All required packages are installed!');
            $this->info('UI Livewire Flux Admin is ready to use.');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Some required packages are missing.');
            $this->error('Please install them before using this package.');
            return Command::FAILURE;
        }
    }

    /**
     * Display information about optional packages.
     */
    protected function displayOptionalPackageInfo(string $package): void
    {
        switch ($package) {
            case 'livewire/flux':
                $this->line('    → Sidebar navigation links will not be automatically injected.');
                $this->line('    → You will need to manually add navigation links to:');
                $this->line('      resources/views/layouts/app/sidebar.blade.php (or components/layouts/app/sidebar.blade.php)');
                $this->line('    → See README.md for the code to add.');
                break;

            case 'laravel/fortify':
                $this->line('    → You will need to manually attach the admin role (ID: 1) to new users.');
                $this->line('    → Update app/Actions/Fortify/CreateNewUser.php to attach role.');
                $this->line('    → See README.md for instructions.');
                break;
        }
    }
}

