<?php

namespace Vormia\UILivewireFluxAdmin\Console\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui-livewireflux-admin:help';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display help information for UI Livewire Flux Admin package commands';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayHeader();
        $this->displayCommands();
        $this->displayUsageExamples();
        $this->displayFooter();
    }

    /**
     * Display the header
     */
    private function displayHeader()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║              UI LIVEWIRE FLUX ADMIN HELP                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->comment('🚀 UI Livewire Flux Admin is a Laravel package for admin panel');
        $this->comment('   components and routes using Livewire Volt and Flux.');
        $this->newLine();
    }

    /**
     * Display available commands
     */
    private function displayCommands()
    {
        $this->info('📋 AVAILABLE COMMANDS:');
        $this->newLine();

        $commands = [
            [
                'command' => 'ui-livewireflux-admin:install',
                'description' => 'Install UI Livewire Flux Admin package with all files and configurations',
                'options' => null
            ],
            [
                'command' => 'ui-livewireflux-admin:help',
                'description' => 'Display this help information',
                'options' => null
            ],
            [
                'command' => 'ui-livewireflux-admin:update',
                'description' => 'Update package files (removes old files and copies fresh ones)',
                'options' => '--force (Skip confirmation prompts)'
            ],
            [
                'command' => 'ui-livewireflux-admin:uninstall',
                'description' => 'Remove all UI Livewire Flux Admin package files and configurations',
                'options' => '--force (Skip confirmation prompts)'
            ],
            [
                'command' => 'ui-livewireflux-admin:check-dependencies',
                'description' => 'Check if all required dependencies are installed',
                'options' => null
            ],
        ];

        foreach ($commands as $cmd) {
            $this->line("  <fg=green>{$cmd['command']}</>");
            $this->line("    {$cmd['description']}");
            if ($cmd['options']) {
                $this->line("    <fg=yellow>Options:</> {$cmd['options']}");
            }
            $this->newLine();
        }
    }

    /**
     * Display usage examples
     */
    private function displayUsageExamples()
    {
        $this->info('💡 USAGE EXAMPLES:');
        $this->newLine();

        $examples = [
            [
                'title' => 'Installation',
                'command' => 'php artisan ui-livewireflux-admin:install',
                'description' => 'Install UI Livewire Flux Admin with all files and configurations'
            ],
            [
                'title' => 'Update Package',
                'command' => 'php artisan ui-livewireflux-admin:update',
                'description' => 'Update all package files to latest version'
            ],
            [
                'title' => 'Force Update',
                'command' => 'php artisan ui-livewireflux-admin:update --force',
                'description' => 'Update without confirmation prompts'
            ],
            [
                'title' => 'Uninstall Package',
                'command' => 'php artisan ui-livewireflux-admin:uninstall',
                'description' => 'Remove all UI Livewire Flux Admin files and configurations'
            ],
            [
                'title' => 'Check Dependencies',
                'command' => 'php artisan ui-livewireflux-admin:check-dependencies',
                'description' => 'Verify all required and optional dependencies are installed'
            ],
        ];

        foreach ($examples as $example) {
            $this->line("  <fg=cyan>{$example['title']}:</>");
            $this->line("    <fg=white>{$example['command']}</>");
            $this->line("    <fg=gray>{$example['description']}</>");
            $this->newLine();
        }
    }

    /**
     * Display package features
     */
    private function displayFeatures()
    {
        $this->info('✨ PACKAGE FEATURES:');
        $this->newLine();

        $features = [
            'AdminPanel Component' => 'Reusable view component for admin panels',
            'Admin Routes' => 'Pre-configured routes for categories, inheritance, locations, availability, and admins',
            'Volt Components' => 'Livewire Volt components for all admin sections',
            'Sidebar Integration' => 'Automatic sidebar menu injection (requires livewire/flux)',
            'Role on Registration' => 'See docs/ROLE-ON-REGISTRATION.md for assigning role on user registration',
            'EnsureUserIsActive' => 'See docs/FORTIFY-IS-ACTIVE.md when using laravel/fortify',
        ];

        foreach ($features as $feature => $description) {
            $this->line("  <fg=green>•</> <fg=white>{$feature}:</> {$description}");
        }

        $this->newLine();
    }

    /**
     * Display requirements information
     */
    private function displayRequirements()
    {
        $this->info('⚙️  REQUIREMENTS:');
        $this->newLine();

        $this->line('  <fg=white>Required:</>');
        $this->line('    • vormiaphp/vormia');
        $this->line('    • livewire/volt');
        $this->newLine();

        $this->line('  <fg=white>Optional:</>');
        $this->line('    • livewire/flux (for automatic sidebar menu)');
        $this->line('    • laravel/fortify (for EnsureUserIsActive - see docs/FORTIFY-IS-ACTIVE.md)');
        $this->newLine();
    }

    /**
     * Display troubleshooting information
     */
    private function displayTroubleshooting()
    {
        $this->info('🔧 TROUBLESHOOTING:');
        $this->newLine();

        $this->line('  <fg=white>Issue:</> Installation fails');
        $this->line('  <fg=gray>Solution:</> Ensure vormiaphp/vormia and livewire/volt are installed');
        $this->newLine();

        $this->line('  <fg=white>Issue:</> Routes not working');
        $this->line('  <fg=gray>Solution:</> Check routes/web.php for proper middleware group');
        $this->newLine();

        $this->line('  <fg=white>Issue:</> Sidebar menu not appearing');
        $this->line('  <fg=gray>Solution:</> Install livewire/flux or manually add menu code');
        $this->newLine();
    }

    /**
     * Display footer
     */
    private function displayFooter()
    {
        $this->displayFeatures();
        $this->displayRequirements();
        $this->displayTroubleshooting();

        $this->info('📚 ADDITIONAL RESOURCES:');
        $this->newLine();

        $this->line('  <fg=white>GitHub:</> https://github.com/vormiaphp/ui-livewireflux-admin');
        $this->line('  <fg=white>Installation:</> composer require vormiaphp/ui-livewireflux-admin');
        $this->line('  <fg=white>Role on registration:</> docs/ROLE-ON-REGISTRATION.md');
        $this->line('  <fg=white>Fortify is_active:</> docs/FORTIFY-IS-ACTIVE.md');

        $this->newLine();
        $this->comment('💡 For role assignment and Fortify setup, see the docs/ directory.');
        $this->newLine();

        $this->info('🎉 Thank you for using UI Livewire Flux Admin!');
        $this->newLine();
    }
}

