<?php

namespace Statamic\StarterKits;

use Exception;
use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Collection;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Path;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

final class InstallableModule extends Module
{
    protected $installer;

    /**
     * Set installer instance.
     *
     * @throws Exception|StarterKitException
     */
    public function installer($installer): self
    {
        $this->installer = $installer;

        return $this;
    }

    /**
     * Validate starter kit module is installable.
     *
     * @throws Exception|StarterKitException
     */
    public function validate(): void
    {
        $this
            ->requireParentInstaller()
            ->ensureModuleConfigNotEmpty()
            ->ensureInstallableFilesExist()
            ->ensureCompatibleDependencies();
    }

    /**
     * Install starter kit module.
     *
     * @throws Exception|StarterKitException
     */
    public function install(): void
    {
        $this
            ->requireParentInstaller()
            ->installFiles()
            ->installDependencies();
    }

    /**
     * Require parent installer instance.
     *
     * @throws Exception
     */
    protected function requireParentInstaller(): self
    {
        if (! $this->installer) {
            throw new Exception('Parent installer required for this operation!');
        }

        return $this;
    }

    /**
     * Install starter kit module files.
     */
    protected function installFiles(): self
    {
        $this->installableFiles()->each(function ($toPath, $fromPath) {
            $this->installFile($fromPath, $toPath, $this->installer->console());
        });

        return $this;
    }

    /**
     * Install starter kit module dependencies.
     */
    protected function installDependencies(): self
    {
        if ($this->installer->withoutDependencies()) {
            return $this;
        }

        if ($packages = $this->installableDependencies('dependencies')) {
            $this->requireDependencies($packages);
        }

        if ($packages = $this->installableDependencies('dependencies_dev')) {
            $this->requireDependencies($packages, true);
        }

        return $this;
    }

    /**
     * Get installable files.
     */
    protected function installableFiles(): Collection
    {
        $installableFromExportPaths = $this
            ->exportPaths()
            ->flatMap(fn ($path) => $this->expandExportDirectoriesToFiles($path));

        $installableFromExportAsPaths = $this
            ->exportAsPaths()
            ->flip()
            ->flatMap(fn ($to, $from) => $this->expandExportDirectoriesToFiles($to, $from));

        return collect()
            ->merge($installableFromExportPaths)
            ->merge($installableFromExportAsPaths);
    }

    /**
     * Expand export path to `[$from => $to]` array format, normalizing directories to files.
     *
     * This is necessary when installing starter kit into existing directories, so that we don't stomp whole directories.
     */
    protected function expandExportDirectoriesToFiles(string $to, ?string $from = null): Collection
    {
        $to = Path::tidy($this->starterKitPath($to));
        $from = Path::tidy($from ? $this->starterKitPath($from) : $to);

        $paths = collect([$from => $to]);

        if ($this->files->isDirectory($from)) {
            $paths = collect($this->files->allFiles($from))
                ->map
                ->getPathname()
                ->mapWithKeys(fn ($path) => [
                    $path => str_replace($from, $to, $path),
                ]);
        }

        $package = $this->installer->package();

        return $paths->mapWithKeys(fn ($to, $from) => [
            Path::tidy($from) => Path::tidy(str_replace("/vendor/{$package}", '', $to)),
        ]);
    }

    /**
     * Install dependency permanently into app.
     */
    protected function requireDependencies(array $packages, bool $dev = false): void
    {
        $args = array_merge(['require'], $this->normalizePackagesArrayToRequireArgs($packages));

        if ($dev) {
            $args[] = '--dev';
        }

        try {
            Composer::withoutQueue()->throwOnFailure()->runAndOperateOnOutput($args, function ($output) {
                return $this->outputFromSymfonyProcess($output);
            });
        } catch (ProcessException $exception) {
            $this->installer->console()->error('Error installing dependencies.');
        }
    }

    /**
     * Get installable dependencies from appropriate require key in composer.json.
     */
    protected function installableDependencies(string $configKey): array
    {
        return collect($this->config($configKey))
            ->filter(fn ($version, $package) => Str::contains($package, '/'))
            ->all();
    }

    /**
     * Ensure installable files exist.
     *
     * @throws StarterKitException
     */
    protected function ensureInstallableFilesExist(): self
    {
        $this
            ->exportPaths()
            ->merge($this->exportAsPaths())
            ->reject(fn ($path) => $this->files->exists($this->starterKitPath($path)))
            ->each(function ($path) {
                throw new StarterKitException("Starter kit path [{$path}] does not exist.");
            });

        return $this;
    }

    /**
     * Ensure compatible dependencies by performing a dry-run.
     */
    protected function ensureCompatibleDependencies(): self
    {
        if ($this->installer->withoutDependencies() || $this->installer->force()) {
            return $this;
        }

        if ($packages = $this->installableDependencies('dependencies')) {
            $this->ensureCanRequireDependencies($packages);
        }

        if ($packages = $this->installableDependencies('dependencies_dev')) {
            $this->ensureCanRequireDependencies($packages, true);
        }

        return $this;
    }

    /**
     * Ensure dependencies are installable by performing a dry-run.
     */
    protected function ensureCanRequireDependencies(array $packages, bool $dev = false): void
    {
        $requireMethod = $dev ? 'requireMultipleDev' : 'requireMultiple';

        try {
            Composer::withoutQueue()->throwOnFailure()->{$requireMethod}($packages, '--dry-run');
        } catch (ProcessException $exception) {
            $this->installer->rollbackWithError('Cannot install due to dependency conflict.', $exception->getMessage());
        }
    }

    /**
     * Get starter kit vendor path.
     */
    protected function starterKitPath(?string $path = null): string
    {
        $package = $this->installer->package();

        return collect([base_path("vendor/{$package}"), $path])->filter()->implode('/');
    }

    /**
     * Normalize packages array to require args, with version handling if `package => version` array structure is passed.
     */
    protected function normalizePackagesArrayToRequireArgs(array $packages): array
    {
        return collect($packages)
            ->map(function ($value, $key) {
                return Str::contains($key, '/')
                    ? "{$key}:{$value}"
                    : "{$value}";
            })
            ->values()
            ->all();
    }

    /**
     * Clean up symfony process output and output to cli.
     */
    protected function outputFromSymfonyProcess(string $output): string
    {
        // Remove terminal color codes.
        $output = preg_replace('/\\e\[[0-9]+m/', '', $output);

        // Remove new lines.
        $output = preg_replace('/[\r\n]+$/', '', $output);

        // If not a blank line, output to terminal.
        if (! empty(trim($output))) {
            $this->installer->console()->line($output);
        }

        return $output;
    }
}
