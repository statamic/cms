<?php

namespace Statamic\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Blink;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

class Installer
{
    protected $files;
    protected $withConfig;
    protected $withoutDependencies;
    protected $force;
    protected $package;
    protected $console;
    protected $url;

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
            ->backupComposerJson()
            ->detectRepositoryUrl()
            ->prepareRepository()
            ->requireStarterKit()
            ->ensureConfig()
            ->ensureExportPathsExist()
            ->ensureCompatibleDependencies()
            ->installFiles()
            ->installDependencies()
            ->reticulateSplines()
            ->removeStarterKit()
            ->removeRepository();
    }

    /**
     * Backup composer.json file.
     *
     * @return $this
     */
    protected function backupComposerJson()
    {
        $this->files->copy(base_path('composer.json'), base_path('composer.json.bak'));

        return $this;
    }

    /**
     * Detect repository url.
     *
     * @return $this
     */
    protected function detectRepositoryUrl()
    {
        if (Http::get("https://repo.packagist.org/p2/{$this->package}.json")->status() === 200) {
            return $this;
        }

        if (Http::get($githubUrl = "https://github.com/{$this->package}")->status() === 200) {
            $this->url = $githubUrl;
        } elseif (Http::get($bitbucketUrl = "https://bitbucket.org/{$this->package}.git")->status() === 200) {
            $this->url = $bitbucketUrl;
        } elseif (Http::get($gitlabUrl = "https://gitlab.com/{$this->package}")->status() === 200) {
            $this->url = $gitlabUrl;
        }

        return $this;
    }

    /**
     * Prepare repository.
     *
     * @return $this
     */
    protected function prepareRepository()
    {
        if (! $this->url) {
            return $this;
        }

        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $composerJson['repositories'][] = [
            'type' => 'vcs',
            'url' => $this->url,
        ];

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        Blink::put('starter-kit-repository-added', $this->url);

        return $this;
    }

    /**
     * Require starter kit dependency.
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
     * Ensure export paths exist.
     *
     * @return $this
     * @throws StarterKitException
     */
    protected function ensureExportPathsExist()
    {
        $this->exportPaths()
             ->reject(function ($path) {
                 return $this->files->exists($this->starterKitPath($path));
             })
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
        if ($this->withoutDependencies || $this->force) {
            return $this;
        }

        $this->installableDependencies('dependencies')->each(function ($version, $package) {
            $this->ensureCompatibleDependency($package, $version);
        });

        $this->installableDependencies('dependencies_dev')->each(function ($version, $package) {
            $this->ensureCompatibleDependency($package, $version, true);
        });

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

        $this->installableDependencies('dependencies')->each(function ($version, $package) {
            $this->installDependency($package, $version);
        });

        $this->installableDependencies('dependencies_dev')->each(function ($version, $package) {
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
        $requireMethod = $dev ? 'requireDev' : 'require';

        try {
            Composer::withoutQueue()->throwOnFailure()->{$requireMethod}($package, $version, '--dry-run');
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

        $args = ['require'];

        if ($dev) {
            $args[] = '--dev';
        }

        $args[] = $package;
        $args[] = $version;

        try {
            Composer::withoutQueue()->throwOnFailure()->runAndOperateOnOutput($args, function ($output) {
                return $this->outputFromSymfonyProcess($output);
            });
        } catch (ProcessException $exception) {
            $this->console->error("Error installing [{$package}].");
        }
    }

    /**
     * Reticulate splines.
     *
     * @return $this
     */
    protected function reticulateSplines()
    {
        $this->console->info('Reticulating splines...');

        if (config('app.env') !== 'testing') {
            sleep(2);
        }

        return $this;
    }

    /**
     * Remove starter kit dependency.
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
     * Remove repository.
     *
     * @return $this
     */
    protected function removeRepository()
    {
        if (! $this->url) {
            return $this;
        }

        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $repositories = collect($composerJson['repositories'])->reject(function ($repository) {
            return isset($repository['url']) && $repository['url'] === $this->url;
        });

        if ($repositories->isNotEmpty()) {
            $composerJson['repositories'] = $repositories;
        } else {
            unset($composerJson['repositories']);
        }

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        return $this;
    }

    /**
     * Backup composer.json file.
     *
     * @return $this
     */
    protected function restoreComposerJson()
    {
        $this->files->copy(base_path('composer.json.bak'), base_path('composer.json'));

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
        $this
            ->removeStarterKit()
            ->restoreComposerJson();

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
     * Get export paths.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function exportPaths()
    {
        $config = YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml')));

        return collect($config['export_paths'] ?? []);
    }

    /**
     * Get installable files.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function installableFiles()
    {
        return $this
            ->exportPaths()
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
     * Get starter kit config.
     *
     * @return mixed
     */
    protected function config($key = null)
    {
        $config = collect(YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml'))));

        if ($key) {
            return $config->get($key);
        }

        return $config;
    }

    /**
     * Get installable dependencies from appropriate require key in composer.json.
     *
     * @param string $configKey
     * @return \Illuminate\Support\Collection
     */
    protected function installableDependencies($configKey)
    {
        return collect($this->config($configKey))->filter(function ($version, $package) {
            return Str::contains($package, '/');
        });
    }
}
