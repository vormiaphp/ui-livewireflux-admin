<?php

namespace Vormia\UILivewireFluxAdmin;

use Illuminate\Support\ServiceProvider;
use Vormia\UILivewireFluxAdmin\Console\Commands\CheckDependenciesCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\HelpCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\InstallCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\UninstallCommand;
use Vormia\UILivewireFluxAdmin\Console\Commands\UpdateCommand;

class UILivewireFluxAdminServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                UninstallCommand::class,
                UpdateCommand::class,
                CheckDependenciesCommand::class,
                HelpCommand::class,
            ]);
        }
    }
}
