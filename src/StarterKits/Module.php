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
     * Instantiate starter kit module installer.
     *
     * @return \Illuminate\Support\Collection
     */
    public function __construct(protected Collection $config, protected Installer $installer)
    {
        $this->files = app(Filesystem::class);
    }

    /**
     * Get module config.
     *
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    protected function config($key = null)
    {
        if ($key) {
            return $this->config->get($key);
        }

        return $this->config;
    }

    /**
     * Get starter kit vendor path.
     *
     * @return string
     */
    protected function starterKitPath($path = null)
    {
        return collect([base_path("vendor/{$this->installer->package}"), $path])->filter()->implode('/');
    }

    /**
     * Validate starter kit module.
     *
     * @throws StarterKitException
     */
    public function validate()
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
    public function install()
    {
        $this
            ->installFiles()
            ->installDependencies();
    }

    /**
     * Install starter kit module files.
     *
     * @return $this
     */
    protected function installFiles()
    {
        $this->installer->console->info('Installing files...');

        $this->installableFiles()->each(function ($toPath, $fromPath) {
            $this->installFile($fromPath, $toPath, $this->installer->console);
        });

        return $this;
    }

    /**
     * Install starter kit module dependencies.
     *
     * @return $this
     */
    protected function installDependencies()
    {
        if ($this->installer->withoutDependencies) {
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
     *
     * @return \Illuminate\Support\Collection
     */
    protected function installableFiles()
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
     *
     * @return \Illuminate\Support\Collection
     */
    protected function exportPaths()
    {
        return collect($this->config('export_paths') ?? []);
    }

    /**
     * Get `export_as` paths (to be renamed on install) from config.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function exportAsPaths()
    {
        return collect($this->config('export_as') ?? []);
    }

    /**
     * Expand config export path to `[$from => $to]` array format, normalizing directories to files.
     *
     * @param  string  $to
     * @param  string  $from
     * @return \Illuminate\Support\Collection
     */
    protected function expandConfigExportPaths($to, $from = null)
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

        return $paths->mapWithKeys(fn ($to, $from) => [
            Path::tidy($from) => Path::tidy(str_replace("/vendor/{$this->installer->package}", '', $to)),
        ]);
    }

    /**
     * Install dependency permanently into app.
     *
     * @param  array  $packages
     * @param  bool  $dev
     */
    protected function requireDependencies($packages, $dev = false)
    {
        if ($dev) {
            $this->installer->console->info('Installing development dependencies...');
        } else {
            $this->installer->console->info('Installing dependencies...');
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
            $this->installer->console->error('Error installing dependencies.');
        }
    }

    /**
     * Clean up symfony process output and output to cli.
     *
     * @return string
     */
    protected function outputFromSymfonyProcess(string $output)
    {
        // Remove terminal color codes.
        $output = preg_replace('/\\e\[[0-9]+m/', '', $output);

        // Remove new lines.
        $output = preg_replace('/[\r\n]+$/', '', $output);

        // If not a blank line, output to terminal.
        if (! empty(trim($output))) {
            $this->installer->console->line($output);
        }

        return $output;
    }

    /**
     * Get installable dependencies from appropriate require key in composer.json.
     *
     * @param  string  $configKey
     * @return array
     */
    protected function installableDependencies($configKey)
    {
        return collect($this->config($configKey))
            ->filter(fn ($version, $package) => Str::contains($package, '/'))
            ->all();
    }

    /**
     * Ensure export paths exist.
     *
     * @return $this
     *
     * @throws StarterKitException
     */
    protected function ensureExportPathsExist()
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
     *
     * @return $this
     */
    protected function ensureCompatibleDependencies()
    {
        if ($this->installer->withoutDependencies || $this->installer->force) {
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
     *
     * @param  array  $packages
     * @param  bool  $dev
     */
    protected function ensureCanRequireDependencies($packages, $dev = false)
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
     *
     * @return array
     */
    protected function normalizePackagesArrayToRequireArgs(array $packages)
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
}
