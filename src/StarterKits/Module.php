<?php

namespace Statamic\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Path;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

final class Module
{
    use Concerns\InteractsWithFilesystem;

    protected $files;

    /**
     * Instantiate starter kit module.
     */
    public function __construct(protected Collection $config, protected Installer $installer)
    {
        $this->files = app(Filesystem::class);
    }

    /**
     * Validate starter kit module.
     *
     * @throws StarterKitException
     */
    public function validate(): void
    {
        $this
            ->ensureExportPathsExist()
            ->ensureCompatibleDependencies();
    }

    /**
     * Install starter kit module.
     *
     * @throws StarterKitException
     */
    public function install(): void
    {
        $this
            ->installFiles()
            ->installDependencies();
    }

    /**
     * Install starter kit module files.
     */
    protected function installFiles(): self
    {
        $this->installer->console()->info('Installing files...');

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
            ->flatMap(fn ($path) => $this->expandConfigExportPaths($path));

        $installableFromExportAsPaths = $this
            ->exportAsPaths()
            ->flip()
            ->flatMap(fn ($to, $from) => $this->expandConfigExportPaths($to, $from));

        return collect()
            ->merge($installableFromExportPaths)
            ->merge($installableFromExportAsPaths);
    }

    /**
     * Get `export_paths` paths from config.
     */
    protected function exportPaths(): Collection
    {
        return collect($this->config('export_paths') ?? []);
    }

    /**
     * Get `export_as` paths (to be renamed on install) from config.
     */
    protected function exportAsPaths(): Collection
    {
        return collect($this->config('export_as') ?? []);
    }

    /**
     * Expand config export path to `[$from => $to]` array format, normalizing directories to files.
     */
    protected function expandConfigExportPaths(string $to, ?string $from = null): Collection
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
        if ($dev) {
            $this->installer->console()->info('Installing development dependencies...');
        } else {
            $this->installer->console()->info('Installing dependencies...');
        }

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
     * Ensure export paths exist.
     *
     * @throws StarterKitException
     */
    protected function ensureExportPathsExist(): self
    {
        $this
            ->exportPaths()
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
     * Get starter kit vendor path.
     */
    protected function starterKitPath(?string $path = null): string
    {
        $package = $this->installer->package();

        return collect([base_path("vendor/{$package}"), $path])->filter()->implode('/');
    }

    /**
     * Get module config.
     */
    protected function config(?string $key = null): mixed
    {
        if ($key) {
            return $this->config->get($key);
        }

        return $this->config;
    }
}
