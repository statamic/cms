<?php

namespace Statamic\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Console\Processes\TtyDetector;
use Facades\Statamic\StarterKits\Hook;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Statamic\Console\NullConsole;
use Statamic\Console\Please\Application as PleaseApplication;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Blink;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

final class Installer
{
    protected $package;
    protected $branch;
    protected $licenseManager;
    protected $files;
    protected $fromLocalRepo;
    protected $withConfig;
    protected $withoutDependencies;
    protected $withUser;
    protected $usingSubProcess;
    protected $force;
    protected $console;
    protected $url;
    protected $disableCleanup;

    /**
     * Instantiate starter kit installer.
     *
     * @param  mixed  $console
     */
    public function __construct(string $package, $console = null, ?LicenseManager $licenseManager = null)
    {
        $this->package = $package;

        $this->licenseManager = $licenseManager;

        $this->console = $console ?? new Nullconsole;

        $this->files = app(Filesystem::class);
    }

    /**
     * Instantiate starter kit installer.
     *
     * @param  mixed  $console
     * @return static
     */
    public static function package(string $package, $console = null, ?LicenseManager $licenseManager = null)
    {
        return new self($package, $console, $licenseManager);
    }

    /**
     * Install from specific branch.
     *
     * @param  string|null  $branch
     * @return $this
     */
    public function branch($branch = null)
    {
        $this->branch = $branch;

        return $this;
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
     * Install using sub-process.
     *
     * @param  bool  $usingSubProcess
     * @return $this
     */
    public function usingSubProcess($usingSubProcess = false)
    {
        $this->usingSubProcess = $usingSubProcess;

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
            ->runPostInstallHook()
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

        $package = $this->branch
            ? "{$this->package}:{$this->branch}"
            : $this->package;

        try {
            Composer::withoutQueue()->throwOnFailure()->requireDev($package);
        } catch (ProcessException $exception) {
            $this->rollbackWithError("Error installing starter kit [{$package}].", $exception->getMessage());
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
        $this
            ->exportPaths()
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

        if ($packages = $this->installableDependencies('dependencies')) {
            $this->ensureCanRequireDependencies($packages);
        }

        if ($packages = $this->installableDependencies('dependencies_dev')) {
            $this->ensureCanRequireDependencies($packages, true);
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
            $this->copyStarterKitConfig();
            $this->copyStarterKitHooks();
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
        if (! $this->withConfig) {
            return;
        }

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
     * Copy starter kit hook scripts.
     */
    protected function copyStarterKitHooks()
    {
        if (! $this->withConfig) {
            return;
        }

        $hooks = ['StarterKitPostInstall.php'];

        collect($hooks)
            ->filter(fn ($hook) => $this->files->exists($this->starterKitPath($hook)))
            ->each(fn ($hook) => $this->copyFile($this->starterKitPath($hook), base_path($hook)));
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

        if ($packages = $this->installableDependencies('dependencies')) {
            $this->requireDependencies($packages);
        }

        if ($packages = $this->installableDependencies('dependencies_dev')) {
            $this->requireDependencies($packages, true);
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
            $this->rollbackWithError('Cannot install due to dependency conflict.', $exception->getMessage());
        }
    }

    /**
     * Install starter kit dependency permanently into app.
     *
     * @param  array  $packages
     * @param  bool  $dev
     */
    protected function requireDependencies($packages, $dev = false)
    {
        if ($dev) {
            $this->console->info('Installing development dependencies...');
        } else {
            $this->console->info('Installing dependencies...');
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
            $this->console->error('Error installing dependencies.');
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
     * Run post-install hook, if one exists in the starter kit.
     *
     * @return $this
     *
     * @throws StarterKitException
     */
    public function runPostInstallHook($throwExceptions = false)
    {
        $postInstallHook = Hook::find($this->starterKitPath('StarterKitPostInstall.php'));

        if ($throwExceptions && ! $postInstallHook) {
            throw new StarterKitException("Cannot find post-install hook for [$this->package].");
        } elseif (! $postInstallHook) {
            return $this;
        }

        if ($this->usingSubProcess && ! TtyDetector::isTtySupported()) {
            return $this->cachePostInstallInstructions();
        }

        if (isset($postInstallHook->registerCommands)) {
            foreach ($postInstallHook->registerCommands as $command) {
                $this->registerInstalledCommand($command);
            }
        }

        $postInstallHook->handle($this->console);

        return $this;
    }

    /**
     * Cache post install instructions for parent process (ie. statamic/cli installer).
     *
     * @return $this
     */
    protected function cachePostInstallInstructions()
    {
        $path = $this->preparePath(storage_path('statamic/tmp/cli/post-install-instructions.txt'));

        $instructions = <<<"EOT"
Warning: TTY not supported in this environment!
To complete this installation, run the following command from your new site directory:
php please starter-kit:run-post-install $this->package
EOT;

        $this->files->put($path, $instructions);

        $this->disableCleanup = true;

        return $this;
    }

    /**
     * Register starter kit installed command for post install hook.
     *
     * @param  string  $commandClass
     */
    protected function registerInstalledCommand($commandClass)
    {
        $app = $this->console->getApplication();

        $command = new $commandClass($app);

        if ($app instanceof PleaseApplication) {
            $command->setRunningInPlease();
            $command->removeStatamicGrouping();
            $command->setHiddenInPlease();
        }

        $app->add($command);
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
            usleep(500000);
        }

        return $this;
    }

    /**
     * Remove starter kit dependency.
     *
     * @return $this
     */
    public function removeStarterKit()
    {
        if ($this->disableCleanup) {
            return $this;
        }

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
        if (Str::contains($output, 'github.com') && Str::contains($output, ['access', 'permission', 'credential', 'authenticate'])) {
            return collect([
                'Composer could not authenticate with GitHub!',
                'Please generate a personal access token at: https://github.com/settings/tokens/new',
                'Then save your token for future use by running the following command:',
                'composer config --global --auth github-oauth.github.com [your-token-here]',
            ])->implode(PHP_EOL);
        }

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
     * Get `export_paths` paths from config.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function exportPaths()
    {
        $config = YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml')));

        return collect($config['export_paths'] ?? []);
    }

    /**
     * Get `export_as` paths (to be renamed on install) from config.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function exportAsPaths()
    {
        $config = YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml')));

        return collect($config['export_as'] ?? []);
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
            ->flatMap(function ($path) {
                return $this->expandConfigExportPaths($path);
            });

        $installableFromExportAsPaths = $this
            ->exportAsPaths()
            ->flip()
            ->flatMap(function ($to, $from) {
                return $this->expandConfigExportPaths($to, $from);
            });

        return collect()
            ->merge($installableFromExportPaths)
            ->merge($installableFromExportAsPaths);
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
                ->map->getPathname()
                ->mapWithKeys(function ($path) use ($from, $to) {
                    return [$path => str_replace($from, $to, $path)];
                });
        }

        return $paths->mapWithKeys(function ($to, $from) {
            return [Path::tidy($from) => Path::tidy(str_replace("/vendor/{$this->package}", '', $to))];
        });
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
            : preg_replace('/(.*)\/[^\/]*/', '$1', Path::tidy($path));

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        return Path::tidy($path);
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
     * @return array
     */
    protected function installableDependencies($configKey)
    {
        return collect($this->config($configKey))->filter(function ($version, $package) {
            return Str::contains($package, '/');
        })->all();
    }

    /**
     * Normalize packages array to require args, with version handling if `package => version` array structure is passed.
     *
     * @return array
     */
    private function normalizePackagesArrayToRequireArgs(array $packages)
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
