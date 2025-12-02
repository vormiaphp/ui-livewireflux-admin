<?php

namespace Vormia\UILivewireFluxAdmin;

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class UILivewireFlux
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Create a new UILivewireFlux instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Get the name of the vormia kit.
     */
    public function name(): string
    {
        return 'UILivewireFlux';
    }

    /**
     * Get the description of the vormia kit.
     */
    public function description(): string
    {
        return 'Vormia UI Livewire Flux Admin - Admin panel components and routes';
    }

    /**
     * Install the vormia kit.
     */
    public function install(): bool
    {
        try {
            $this->copyStubs();
            return true;
        } catch (\Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Update the vormia kit.
     */
    public function update(): bool
    {
        try {
            $this->updateStubs();
            return true;
        } catch (\Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Uninstall the vormia kit.
     */
    public function uninstall(): bool
    {
        try {
            $this->removeInstalledFiles();
            return true;
        } catch (\Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Copy all stubs to their respective directories.
     */
    protected function copyStubs(): void
    {
        // Copy app files
        $appSource = __DIR__ . '/stubs/app';
        $appDest = $this->appPath();
        if ($this->filesystem->isDirectory($appSource)) {
            $this->copyDirectory($appSource, $appDest);
        }

        // Copy view files
        $viewsSource = __DIR__ . '/stubs/resources/views';
        $viewsDest = $this->resourcePath('views');
        if ($this->filesystem->isDirectory($viewsSource)) {
            $this->copyDirectory($viewsSource, $viewsDest);
        }
    }

    /**
     * Update Stubs in directories.
     */
    protected function updateStubs(): void
    {
        // Copy app files
        $appSource = __DIR__ . '/stubs/app';
        $appDest = $this->appPath();
        if ($this->filesystem->isDirectory($appSource)) {
            $this->updateDirectory($appSource, $appDest);
        }

        // Copy view files
        $viewsSource = __DIR__ . '/stubs/resources/views';
        $viewsDest = $this->resourcePath('views');
        if ($this->filesystem->isDirectory($viewsSource)) {
            $this->updateDirectory($viewsSource, $viewsDest);
        }
    }

    /**
     * Handle errors during installation/update.
     *
     * @param \Exception $e
     * @throws \Exception
     */
    protected function handleError(\Exception $e): void
    {
        Log::error('UI Livewire Flux Admin installation error: ' . $e->getMessage());
        throw $e;
    }

    /**
     * Copy a directory from source to destination.
     */
    protected function copyDirectory(string $source, string $destination): void
    {
        if (!$this->filesystem->exists($source)) {
            throw new RuntimeException("Source directory does not exist: {$source}");
        }

        $this->filesystem->ensureDirectoryExists(dirname($destination));

        foreach ($this->filesystem->allFiles($source) as $file) {
            $relativePath = ltrim(str_replace($source, '', $file->getPathname()), '/\\');
            $destFile = rtrim($destination, '/\\') . '/' . $relativePath;

            if ($this->filesystem->exists($destFile)) {
                if (app()->runningInConsole() && app()->bound('command')) {
                    $command = app('command');
                    if (method_exists($command, 'confirm')) {
                        if (!$command->confirm("File {$destFile} already exists. Override?", false)) {
                            $command->line("  Skipped: {$destFile}");
                            continue;
                        }
                    }
                }
            }

            $this->filesystem->ensureDirectoryExists(dirname($destFile));
            $this->filesystem->copy($file->getPathname(), $destFile);
        }
    }

    /**
     * Get the application path.
     */
    protected function appPath(string $path = ''): string
    {
        return app_path($path);
    }

    /**
     * Get the resources path.
     */
    protected function resourcePath(string $path = ''): string
    {
        return resource_path($path);
    }

    /**
     * Get the base path.
     */
    protected function basePath(string $path = ''): string
    {
        return base_path($path);
    }

    /**
     * Remove installed files during uninstallation.
     */
    protected function removeInstalledFiles(): void
    {
        $filesToRemove = [
            $this->appPath('View/Components/AdminPanel.php'),
            $this->appPath('Actions/Fortify/CreateNewUser.php'),
            $this->resourcePath('views/components/admin-panel.blade.php'),
            $this->resourcePath('views/livewire/admin'),
        ];

        foreach ($filesToRemove as $file) {
            if ($this->filesystem->exists($file)) {
                if ($this->filesystem->isDirectory($file)) {
                    $this->filesystem->deleteDirectory($file);
                } else {
                    $this->filesystem->delete($file);
                }
            }
        }
    }

    /**
     * Update a directory with new files.
     */
    protected function updateDirectory(string $source, string $destination): void
    {
        if (!$this->filesystem->isDirectory($source)) {
            return;
        }

        // Create destination directory if it doesn't exist
        $this->filesystem->ensureDirectoryExists($destination);

        // Copy files
        foreach ($this->filesystem->allFiles($source) as $file) {
            $relativePath = ltrim(str_replace($source, '', $file->getPathname()), '/\\');
            $destPath = rtrim($destination, '/\\') . '/' . $relativePath;

            // Only copy if file doesn't exist or is newer
            if (
                !$this->filesystem->exists($destPath) ||
                $this->filesystem->lastModified($file->getPathname()) > $this->filesystem->lastModified($destPath)
            ) {
                $this->filesystem->ensureDirectoryExists(dirname($destPath));
                $this->filesystem->copy($file->getPathname(), $destPath);
            }
        }
    }
}
