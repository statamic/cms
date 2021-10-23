<?php

namespace Statamic\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Statamic\Console\NullConsole;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Blink;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

final class Installer
{
    protected $package;
    protected $licenseManager;
    protected $files;
    protected $fromLocalRepo;
    protected $withConfig;
    protected $withoutDependencies;
    protected $withUser;
    protected $force;
    protected $console;
    protected $url;

    /**
     * Instantiate starter kit installer.
     *
     * @param  string  $package
     * @param  LicenseManager  $licenseManager
     * @param  mixed  $console
     */
    public function __construct(string $package, LicenseManager $licenseManager, $console = null)
    {
        $this->package = $package;
        $this->licenseManager = $licenseManager;
        $this->console = $console ?? new Nullconsole;

        $this->files = app(Filesystem::class);
    }

    /**
     * Instantiate starter kit installer.
     *
     * @param  string  $package
     * @param  LicenseManager  $licenseManager
     * @param  mixed  $console
     * @return static
     */
    public static function package(string $package, LicenseManager $licenseManager, $console = null)
    {
        return new static($package, $licenseManager, $console);
    }

    /**
     * Install from local repo configured in composer config.json.
     *
     * @param  bool  $fromLocalRepo
     * @return $this
     */
    public function fromLocalRepo($fromLocalRepo = false)
    {
        $this->fromLocalRepo = $fromLocalRepo;

        return $this;
    }

    /**
     * Install with starter-kit config for local development purposes.
     *
     * @param  bool  $withConfig
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
     * @param  bool  $withoutDependencies
     * @return $this
     */
    public function withoutDependencies($withoutDependencies = false)
    {
        $this->withoutDependencies = $withoutDependencies;

        return $this;
    }

    /**
     * Install with super user.
     *
     * @param  bool  $withUser
     * @return $this
     */
    public function withUser($withUser = false)
    {
        $this->withUser = $withUser;

        return $this;
    }

    /**
     * Force install and allow dependency errors.
     *
     * @param  bool  $force
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
     * @throws StarterKitException
     */
    public function install()
    {
        $this
            ->validateLicense()
            ->backupComposerJson()
            ->detectRepositoryUrl()
            ->prepareRepository()
            ->requireStarterKit()
            ->ensureConfig()
            ->ensureExportPathsExist()
            ->ensureCompatibleDependencies()
            ->installFiles()
            ->installDependencies()
            ->makeSuperUser()
            ->reticulateSplines()
            ->removeStarterKit()
            ->removeRepository()
            ->removeComposerJsonBackup()
            ->completeInstall();
    }

    /**
     * Check with license manager to determine whether or not to continue with installation.
     *
     * @return $this
     */
    protected function validateLicense()
    {
        if (! $this->licenseManager->isValid()) {
            throw new StarterKitException;
        }

        return $this;
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
        if ($this->fromLocalRepo) {
            return $this;
        }

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
        if ($this->fromLocalRepo || ! $this->url) {
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
            $this->rollbackWithError("Error installing starter kit [{$this->package}].", $exception->getMessage());
        }

        return $this;
    }

    /**
     * Ensure starter kit has config.
     *
     * @return $this
     *
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
     *
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
            $this->copyStarterKitConfig();
        }

        return $this;
    }

    /**
     * Copy starter kit file.
     *
     * @param  mixed  $fromPath
     * @param  mixed  $toPath
     */
    protected function copyFile($fromPath, $toPath)
    {
        $displayPath = str_replace(Path::tidy(base_path().'/'), '', $toPath);

        $this->console->line("Installing file [{$displayPath}]");

        $this->files->copy($fromPath, $this->preparePath($toPath));
    }

    /**
     * Copy starter kit config without versions, to encourage dependency management using composer.
     */
    protected function copyStarterKitConfig()
    {
        if ($this->withoutDependencies) {
            return $this->copyFile($this->starterKitPath('starter-kit.yaml'), base_path('starter-kit.yaml'));
        }

        $this->console->line('Installing file [starter-kit.yaml]');

        $config = collect(YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml'))));

        $dependencies = collect()
            ->merge($config->get('dependencies'))
            ->merge($config->get('dependencies_dev'));

        $config
            ->forget('dependencies')
            ->forget('dependencies_dev');

        if ($dependencies->isNotEmpty()) {
            $config->put('dependencies', $dependencies->keys()->all());
        }

        $this->files->put(base_path('starter-kit.yaml'), YAML::dump($config->all()));
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
     * @param  string  $package
     * @param  string  $version
     * @param  bool  $dev
     */
    protected function ensureCompatibleDependency($package, $version, $dev = false)
    {
        $requireMethod = $dev ? 'requireDev' : 'require';

        try {
            Composer::withoutQueue()->throwOnFailure()->{$requireMethod}($package, $version, '--dry-run');
        } catch (ProcessException $exception) {
            $this->rollbackWithError("Cannot install due to error with [{$package}] dependency.", $exception->getMessage());
        }
    }

    /**
     * Install starter kit dependency permanently into app.
     *
     * @param  string  $package
     * @param  string  $version
     * @param  bool  $dev
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
     * Make super user.
     *
     * @return $this
     */
    public function makeSuperUser()
    {
        if (! $this->withUser) {
            return $this;
        }

        if ($this->console->confirm('Create a super user?', false)) {
            $this->console->call('make:user', ['--super' => true]);
        }

        return $this;
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

        if (Composer::isInstalled($this->package)) {
            Composer::withoutQueue()->throwOnFailure(false)->removeDev($this->package);
        }

        return $this;
    }

    /**
     * Remove composer.json backup.
     *
     * @return $this
     */
    protected function removeComposerJsonBackup()
    {
        $this->files->delete(base_path('composer.json.bak'));

        return $this;
    }

    /**
     * Complete starter kit install, expiring license key and/or incrementing install count.
     *
     * @return $this
     */
    protected function completeInstall()
    {
        $this->licenseManager->completeInstall();

        return $this;
    }

    /**
     * Remove repository.
     *
     * @return $this
     */
    protected function removeRepository()
    {
        if ($this->fromLocalRepo || ! $this->url) {
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
     * Restore composer.json file.
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
     * @param  string  $error
     * @param  string|null  $output
     *
     * @throws StarterKitException
     */
    protected function rollbackWithError($error, $output = null)
    {
        $this
            ->removeStarterKit()
            ->restoreComposerJson()
            ->removeComposerJsonBackup();

        if ($output) {
            $this->console->line($this->tidyComposerErrorOutput($output));
        }

        throw new StarterKitException($error);
    }

    /**
     * Remove the `require [--dev] [--dry-run] [--prefer-source]...` stuff from the end of composer error output.
     *
     * @param  string  $output
     * @return string
     */
    protected function tidyComposerErrorOutput($output)
    {
        return preg_replace("/\\n\\nrequire \[.*$/", '', $output);
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
     * @param  string  $output
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
                $path = Path::tidy($path);

                return [$path => str_replace("/vendor/{$this->package}", '', $path)];
            });
    }

    /**
     * Expand export paths.
     *
     * @param  string  $path
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
     * @param  string  $path
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
     * @param  string  $configKey
     * @return \Illuminate\Support\Collection
     */
    protected function installableDependencies($configKey)
    {
        return collect($this->config($configKey))->filter(function ($version, $package) {
            return Str::contains($package, '/');
        });
    }
}
