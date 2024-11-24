<?php

namespace Statamic\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Console\Processes\TtyDetector;
use Facades\Statamic\StarterKits\Hook;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Statamic\Console\NullConsole;
use Statamic\Console\Please\Application as PleaseApplication;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Facades\Blink;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Concerns\InteractsWithFilesystem;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

final class Installer
{
    use FluentlyGetsAndSets, InteractsWithFilesystem;

    protected $package;
    protected $branch;
    protected $licenseManager;
    protected $files;
    protected $fromLocalRepo;
    protected $withConfig;
    protected $withoutDependencies;
    protected $withUserPrompt;
    protected $isInteractive;
    protected $usingSubProcess;
    protected $force;
    protected $console;
    protected $url;
    protected $modules;
    protected $disableCleanup;

    /**
     * Instantiate starter kit installer.
     */
    public function __construct(string $package, ?Command $console = null, ?LicenseManager $licenseManager = null)
    {
        $this->package = $package;

        $this->licenseManager = $licenseManager;

        $this->console = $console ?? new NullConsole;

        $this->files = app(Filesystem::class);
    }

    /**
     * Get or set whether to install from specific branch.
     */
    public function branch(?string $branch = null): self|bool|null
    {
        return $this->fluentlyGetOrSet('branch')->args(func_get_args());

        return $this;
    }

    /**
     * Get or set whether to install from local repo configured in composer config.json.
     */
    public function fromLocalRepo(bool $fromLocalRepo = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('fromLocalRepo')->args(func_get_args());

        return $this;
    }

    /**
     * Get or set whether to install with starter-kit config for local development purposes.
     */
    public function withConfig(bool $withConfig = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('withConfig')->args(func_get_args());

        return $this;
    }

    /**
     * Get or set whether to install without dependencies.
     */
    public function withoutDependencies(?bool $withoutDependencies = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('withoutDependencies')->args(func_get_args());
    }

    /**
     * Get or set whether to install with super user prompt.
     */
    public function withUserPrompt(bool $withUserPrompt = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('withUserPrompt')->args(func_get_args());

        return $this;
    }

    /**
     * Get or set whether command is being run interactively.
     */
    public function isInteractive($isInteractive = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('isInteractive')->args(func_get_args());
    }

    /**
     * Get or set whether to install using sub-process.
     */
    public function usingSubProcess(bool $usingSubProcess = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('usingSubProcess')->args(func_get_args());

        return $this;
    }

    /**
     * Get or set whether to force install and allow dependency errors.
     */
    public function force(bool $force = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('force')->args(func_get_args());
    }

    /**
     * Get starter kit package.
     */
    public function package(): string
    {
        return $this->package;
    }

    /**
     * Get console command instance.
     */
    public function console(): Command|NullConsole
    {
        return $this->console;
    }

    /**
     * Install starter kit.
     *
     * @throws StarterKitException
     */
    public function install(): void
    {
        $this
            ->validateLicense()
            ->backupComposerJson()
            ->detectRepositoryUrl()
            ->prepareRepository()
            ->requireStarterKit()
            ->ensureConfig()
            ->instantiateModules()
            ->installModules()
            ->copyStarterKitConfig()
            ->copyStarterKitHooks()
            ->makeSuperUser()
            ->runPostInstallHooks()
            ->reticulateSplines()
            ->removeStarterKit()
            ->removeRepository()
            ->removeComposerJsonBackup()
            ->completeInstall();
    }

    /**
     * Check with license manager to determine whether or not to continue with installation.
     *
     * @throws StarterKitException
     */
    protected function validateLicense(): self
    {
        if (! $this->licenseManager->isValid()) {
            throw new StarterKitException;
        }

        return $this;
    }

    /**
     * Backup composer.json file.
     */
    protected function backupComposerJson(): self
    {
        $this->files->copy(base_path('composer.json'), base_path('composer.json.bak'));

        return $this;
    }

    /**
     * Detect repository url.
     */
    protected function detectRepositoryUrl(): self
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
     */
    protected function prepareRepository(): self
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
     */
    protected function requireStarterKit(): self
    {
        spin(
            function () {
                $package = $this->branch
                    ? "{$this->package}:{$this->branch}"
                    : $this->package;

                try {
                    Composer::withoutQueue()->throwOnFailure()->requireDev($package);
                } catch (ProcessException $exception) {
                    $this->rollbackWithError("Error installing starter kit [{$package}].", $exception->getMessage());
                }
            },
            "Preparing starter kit [{$this->package}]..."
        );

        return $this;
    }

    /**
     * Ensure starter kit has config.
     *
     * @throws StarterKitException
     */
    protected function ensureConfig(): self
    {
        if (! $this->files->exists($this->starterKitPath('starter-kit.yaml'))) {
            throw new StarterKitException('Starter kit config [starter-kit.yaml] does not exist.');
        }

        return $this;
    }

    /**
     * Instantiate and validate modules that are to be installed.
     */
    protected function instantiateModules(): self
    {
        $this->modules = collect(['top_level' => $this->config()->all()])
            ->map(fn ($config, $key) => $this->instantiateModuleRecursively($config, $key))
            ->flatten()
            ->filter()
            ->each(fn ($module) => $module->validate());

        return $this;
    }

    /**
     * Instantiate module and check if nested modules should be recursively instantiated.
     */
    protected function instantiateModuleRecursively(array $config, string $key): InstallableModule|array
    {
        $instantiated = (new InstallableModule($config, $key))->installer($this);

        if ($modules = Arr::get($config, 'modules')) {
            $instantiated = collect($modules)
                ->map(fn ($config, $childKey) => $this->instantiateModule($config, $this->normalizeModuleKey($key, $childKey)))
                ->prepend($instantiated, $key)
                ->filter()
                ->all();
        }

        return $instantiated;
    }

    /**
     * Instantiate individual module.
     */
    protected function instantiateModule(array $config, string $key): InstallableModule|array|bool
    {
        $shouldPrompt = true;

        if (Arr::has($config, 'options')) {
            return $this->instantiateSelectModule($config, $key);
        }

        if (Arr::get($config, 'prompt') === false) {
            $shouldPrompt = false;
        }

        $name = str_replace('_', ' ', $key);

        if ($shouldPrompt && $this->isInteractive && ! confirm(Arr::get($config, 'prompt', "Would you like to install the [{$name}] module?"), false)) {
            return false;
        } elseif ($shouldPrompt && ! $this->isInteractive) {
            return false;
        }

        return $this->instantiateModuleRecursively($config, $key);
    }

    /**
     * Instantiate select module.
     */
    protected function instantiateSelectModule(array $config, string $key): InstallableModule|array|bool
    {
        $skipOptionLabel = Arr::get($config, 'skip_option', 'No');
        $skipModuleValue = 'skip_module';

        $options = collect($config['options'])
            ->map(fn ($option, $optionKey) => Arr::get($option, 'label', ucfirst($optionKey)))
            ->when($skipOptionLabel !== false, fn ($c) => $c->prepend($skipOptionLabel, $skipModuleValue))
            ->all();

        $name = str_replace('_', ' ', $key);

        if ($this->isInteractive) {
            $choice = select(
                label: Arr::get($config, 'prompt', "Would you like to install one of the following [{$name}] modules?"),
                options: $options,
                default: Arr::get($config, 'default'),
            );
        } elseif (! $this->isInteractive && ! $choice = Arr::get($config, 'default')) {
            return false;
        }

        if ($choice === $skipModuleValue) {
            return false;
        }

        $selectedKey = "{$key}_{$choice}";
        $selectedModuleConfig = $config['options'][$choice];

        return $this->instantiateModuleRecursively($selectedModuleConfig, $selectedKey);
    }

    /**
     * Normalize module key.
     */
    protected function normalizeModuleKey(string $key, string $childKey): string
    {
        return $key !== 'top_level' ? "{$key}_{$childKey}" : $childKey;
    }

    /**
     * Install all the modules.
     */
    protected function installModules(): self
    {
        $this->console->info('Installing starter kit...');

        $this->modules->each(fn ($module) => $module->install());

        return $this;
    }

    /**
     * Copy starter kit config without versions, to encourage dependency management using composer.
     */
    protected function copyStarterKitConfig(): self
    {
        if (! $this->withConfig) {
            return $this;
        }

        if ($this->withoutDependencies) {
            return $this->installFile($this->starterKitPath('starter-kit.yaml'), base_path('starter-kit.yaml'), $this->console());
        }

        $this->console->line('Installing file [starter-kit.yaml]');

        $config = $this->config();

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

        return $this;
    }

    /**
     * Copy starter kit hook scripts.
     */
    protected function copyStarterKitHooks(): self
    {
        if (! $this->withConfig) {
            return $this;
        }

        $hooks = ['StarterKitPostInstall.php'];

        collect($hooks)
            ->filter(fn ($hook) => $this->files->exists($this->starterKitPath($hook)))
            ->each(fn ($hook) => $this->installFile($this->starterKitPath($hook), base_path($hook), $this->console()));

        return $this;
    }

    /**
     * Make super user.
     */
    public function makeSuperUser(): self
    {
        if (! $this->withUserPrompt) {
            return $this;
        }

        if (confirm('Create a super user?', false)) {
            $this->console->call('make:user', ['--super' => true]);
        }

        return $this;
    }

    /**
     * Run post-install hook, if one exists in the starter kit.
     *
     * @throws StarterKitException
     */
    public function runPostInstallHooks(bool $throwExceptions = false): self
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
     */
    protected function cachePostInstallInstructions(): self
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
     */
    protected function registerInstalledCommand(string $commandClass): void
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
     * Reticulate splines, to prevent multiple Bézier curves from conjoining at the Maxis point of the starter kit install.
     */
    protected function reticulateSplines(): self
    {
        spin(
            function () {
                if (config('app.env') !== 'testing') {
                    usleep(500000);
                }
            },
            'Reticulating splines...'
        );

        return $this;
    }

    /**
     * Remove starter kit dependency.
     */
    public function removeStarterKit(): self
    {
        if ($this->disableCleanup) {
            return $this;
        }

        spin(
            function () {
                if (Composer::isInstalled($this->package)) {
                    Composer::withoutQueue()->throwOnFailure(false)->removeDev($this->package);
                }
            },
            'Cleaning up temporary files...'
        );

        return $this;
    }

    /**
     * Remove composer.json backup.
     */
    protected function removeComposerJsonBackup(): self
    {
        $this->files->delete(base_path('composer.json.bak'));

        return $this;
    }

    /**
     * Complete starter kit install, expiring license key and/or incrementing install count.
     */
    protected function completeInstall(): self
    {
        $this->licenseManager->completeInstall();

        return $this;
    }

    /**
     * Remove repository.
     */
    protected function removeRepository(): self
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
     */
    protected function restoreComposerJson(): self
    {
        $this->files->copy(base_path('composer.json.bak'), base_path('composer.json'));

        return $this;
    }

    /**
     * Rollback with error.
     *
     * @throws StarterKitException
     */
    public function rollbackWithError(string $error, ?string $output = null): void
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
     */
    protected function tidyComposerErrorOutput(string $output): string
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
     */
    protected function starterKitPath(?string $path = null): string
    {
        return collect([base_path("vendor/{$this->package}"), $path])->filter()->implode('/');
    }

    /**
     * Get starter kit config.
     */
    protected function config(?string $key = null): mixed
    {
        $config = collect(YAML::parse($this->files->get($this->starterKitPath('starter-kit.yaml'))));

        if ($key) {
            return $config->get($key);
        }

        return $config;
    }
}
