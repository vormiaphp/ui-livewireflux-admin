<?php

namespace Vormia\UILivewireFluxAdmin;

use Illuminate\Support\ServiceProvider;
use Vormia\UILivewireFluxAdmin\Console\Commands\InstallCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\HelpCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\UpdateCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\UninstallCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\CheckDependenciesCommand;

class UILivewireFluxAdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the main class
        $this->app->bind('ui-livewireflux-admin', function () {
            return new UILivewireFlux();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check for required packages
        $this->checkRequiredDependencies();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                HelpCommand::class,
                UpdateCommand::class,
                UninstallCommand::class,
                CheckDependenciesCommand::class,
            ]);
        }
    }

    /**
     * Check for required dependencies.
     */
    protected function checkRequiredDependencies(): void
    {
        // Check for required package: vormiaphp/vormia
        // Try different possible namespaces
        $vormiaExists = class_exists('VormiaPHP\Vormia\VormiaServiceProvider') ||
            class_exists('Vormia\Vormia\VormiaServiceProvider');

        if (!$vormiaExists) {
            if ($this->app->runningInConsole()) {
                $this->app['log']->warning(
                    '[UI Livewire Flux Admin] vormiaphp/vormia package is required. ' .
                        'Please install it first: composer require vormiaphp/vormia'
                );
            }
        }

        // Check for required package: livewire/volt
        if (!class_exists('Livewire\Volt\Volt')) {
            if ($this->app->runningInConsole()) {
                $this->app['log']->warning(
                    '[UI Livewire Flux Admin] livewire/volt package is required. ' .
                        'Please install it first: composer require livewire/volt'
                );
            }
        }
    }
}
