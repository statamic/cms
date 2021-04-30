<?php

namespace Statamic\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;

class Installer
{
    protected $files;
    protected $withConfig;
    protected $withoutDependencies;
    protected $force;
    protected $package;
    protected $console;
    protected $composerOutput;

    /**
     * Instantiate starter kit installer.
     */
    public function __construct()
    {
        $this->files = app(Filesystem::class);
    }

    /**
     * Install with starter-kit config for development purposes.
     *
     * @param bool $withConfig
     * @return $this
     */
    public function withConfig($withConfig = false)
    {
        $this->withConfig = $withConfig;

        return $this;
    }

    /**
     * Install without dependencies.
     *
     * @param bool $withoutDependencies
     * @return $this
     */
    public function withoutDependencies($withoutDependencies = false)
    {
        $this->withoutDependencies = $withoutDependencies;

        return $this;
    }

    /**
     * Force install and allow dependency errors.
     *
     * @param bool $force
     * @return $this
     */
    public function force($force = false)
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Install starter kit.
     *
     * @param string $package
     * @param mixed $console
     * @throws StarterKitException
     */
    public function install($package, $console = null)
    {
        $this->package = $package;
        $this->console = $console;

        $this
            // ->checkLicense()
            ->requireStarterKit()
            ->ensureCompatibleDependencies()
            ->ensureConfig()
            ->installFiles()
            ->installDependencies()
            ->reticulateSplines()
            ->removeStarterKit();
    }

    /**
     * Composer require starter kit dependency.
     *
     * @return $this
     */
    protected function requireStarterKit()
    {
        $this->console->info("Preparing starter kit [{$this->package}]...");

        try {
            Composer::withoutQueue()->throwOnFailure()->requireDev($this->package);
        } catch (ProcessException $exception) {
            $this->rollbackWithError("Error installing [{$this->package}].");
        }

        return $this;
    }

    /**
     * Ensure compatible dependencies by performing a dry-run.
     *
     * @return $this
     */
    protected function ensureCompatibleDependencies()
    {
        if ($this->withoutDependencies || $this->force) {
            return $this;
        }

        $this->installableDependencies('require')->each(function ($version, $package) {
            $this->ensureCompatibleDependency($package, $version);
        });

        $this->installableDependencies('require-dev')->each(function ($version, $package) {
            $this->ensureCompatibleDependency($package, $version, true);
        });

        return $this;
    }

    /**
     * Ensure starter kit has config.
     *
     * @return $this
     * @throws StarterKitException
     */
    protected function ensureConfig()
    {
        if (! $this->files->exists($this->starterKitPath('starter-kit.yaml'))) {
            throw new StarterKitException('Starter kit config [starter-kit.yaml] does not exist.');
        }

        return $this;
    }

    /**
     * Install starter kit files.
     *
     * @return $this
     */
    protected function installFiles()
    {
        $this->console->info('Installing files...');

        $this->installableFiles()->each(function ($toPath, $fromPath) {
            $this->copyFile($fromPath, $toPath);
        });

        if ($this->withConfig) {
            $this->copyFile($this->starterKitPath('starter-kit.yaml'), base_path('starter-kit.yaml'));
        }

        return $this;
    }

    /**
     * Copy starter kit file.
     *
     * @param mixed $fromPath
     * @param mixed $toPath
     */
    protected function copyFile($fromPath, $toPath)
    {
        $displayPath = str_replace(base_path().'/', '', $toPath);

        $this->console->line("Installing file [{$displayPath}]");

        $this->files->copy($fromPath, $this->preparePath($toPath));
    }

    /**
     * Install starter kit dependencies.
     *
     * @return $this
     */
    protected function installDependencies()
    {
        if ($this->withoutDependencies) {
            return $this;
        }

        $this->installableDependencies('require')->each(function ($version, $package) {
            $this->installDependency($package, $version);
        });

        $this->installableDependencies('require-dev')->each(function ($version, $package) {
            $this->installDependency($package, $version, true);
        });

        return $this;
    }

    /**
     * Ensure compatible dependency by performing a dry-run.
     *
     * @param string $package
     * @param string $version
     * @param bool $dev
     */
    protected function ensureCompatibleDependency($package, $version, $dev = false)
    {
        $args = $this->requireDependencyArgs($package, $version, $dev, '--dry-run');

        try {
            Composer::withoutQueue()->throwOnFailure()->runComposerCommand(...$args);
        } catch (ProcessException $exception) {
            $this->rollbackWithError("Cannot install due to error with [{$package}] dependency.");
        }
    }

    /**
     * Install starter kit dependency permanently into app.
     *
     * @param string $package
     * @param string $version
     * @param bool $dev
     */
    protected function installDependency($package, $version, $dev = false)
    {
        $this->console->info("Installing dependency [{$package}]...");

        $args = $this->requireDependencyArgs($package, $version, $dev);

        Composer::withoutQueue()->throwOnFailure(false)->runAndOperateOnOutput($args, function ($output) {
            return $this->outputFromSymfonyProcess($output);
        });
    }

    /**
     * Reticulate splines.
     *
     * @return $this
     */
    protected function reticulateSplines()
    {
        $this->console->info('Reticulating splines...');

        sleep(2);

        return $this;
    }

    /**
     * Composer remove starter kit dependency.
     *
     * @return $this
     */
    protected function removeStarterKit()
    {
        $this->console->info('Cleaning up temporary files...');

        Composer::withoutQueue()->throwOnFailure(false)->remove($this->package);

        return $this;
    }

    /**
     * Rollback with error.
     *
     * @param string $error
     * @throws StarterKitException
     */
    protected function rollbackWithError($error)
    {
        $this->removeStarterKit();

        throw new StarterKitException($error);
    }

    /**
     * Get starter kit vendor path.
     *
     * @return string
     */
    protected function starterKitPath($path = null)
    {
        return collect([base_path("vendor/{$this->package}"), $path])->filter()->implode('/');
    }

    /**
     * Clean up symfony process output and output to cli.
     *
     * TODO: Move to trait and reuse in MakeAddon?
     *
     * @param string $output
     * @return string
     */
    private function outputFromSymfonyProcess(string $output)
    {
        // Remove terminal color codes.
        $output = preg_replace('/\\e\[[0-9]+m/', '', $output);

        // Remove new lines.
        $output = preg_replace('/[\r\n]+$/', '', $output);

        // If not a blank line, output to terminal.
        if (! empty(trim($output))) {
            $this->console->line($output);
        }

        return $output;
    }

    /**
     * Get installable files.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function installableFiles()
    {
        $config = YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml')));

        return collect($config['export_paths'] ?? [])
            ->filter(function ($path) {
                return $this->files->exists($this->starterKitPath($path));
            })
            ->flatMap(function ($path) {
                return $this->expandConfigExportPaths($path);
            })
            ->mapWithKeys(function ($path) {
                return [$path => str_replace("/vendor/{$this->package}", '', $path)];
            });
    }

    /**
     * Expand export paths.
     *
     * @param string $path
     */
    protected function expandConfigExportPaths($path)
    {
        $path = $this->starterKitPath($path);

        if ($this->files->isDirectory($path)) {
            return collect($this->files->allFiles($path))->map->getPathname()->all();
        }

        return [$path];
    }

    /**
     * Prepare path directory.
     *
     * @param string $path
     * @return string
     */
    protected function preparePath($path)
    {
        $directory = $this->files->isDirectory($path)
            ? $path
            : preg_replace('/(.*)\/[^\/]*/', '$1', $path);

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        return $path;
    }

    /**
     * Get installable dependencies from appropriate require key in composer.json.
     *
     * @param string $requireKey
     * @return \Illuminate\Support\Collection
     */
    protected function installableDependencies($requireKey)
    {
        $composerJson = json_decode($this->files->get($this->starterKitPath('composer.json')), true);

        return collect($composerJson[$requireKey] ?? [])->filter(function ($version, $package) {
            return $this->dependencies()->contains($package);
        });
    }

    /**
     * Get starter kit dependencies that should be copied from the composer.json.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function dependencies()
    {
        return collect($this->config()->get('dependencies'));
    }

    /**
     * Get require dependency args.
     *
     * @param string $package
     * @param string $version
     * @param bool $dev
     * @param array $extraArgs
     * @return array
     */
    protected function requireDependencyArgs($package, $version, $dev, ...$extraArgs)
    {
        $args = ['require'];

        if ($dev) {
            $args[] = '--dev';
        }

        $args[] = $package;
        $args[] = $version;

        return array_merge($args, $extraArgs);
    }

    /**
     * Get starter kit config.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function config()
    {
        return collect(YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml'))));
    }
}
