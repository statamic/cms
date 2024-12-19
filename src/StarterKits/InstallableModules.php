<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Str;

final class InstallableModules
{
    protected $config;
    protected $starterKitPath;
    protected $files;
    protected $starterKit;
    protected $installer;
    protected $modules;

    /**
     * Create installable modules helper.
     */
    public function __construct(Collection|array $config, string $starterKitPath)
    {
        $this->config = collect($config);

        $this->starterKitPath = $starterKitPath;

        $this->files = app(Filesystem::class);
    }

    /**
     * Set installer instance.
     */
    public function installer(?Installer $installer): self
    {
        $this->installer = $installer;

        return $this;
    }

    /**
     * Get all modules.
     */
    public function all(): Collection
    {
        return $this->modules;
    }

    /**
     * Flatten all modules.
     */
    public function flatten(): self
    {
        $this->modules = self::flattenModules($this->modules);

        return $this;
    }

    /**
     * Instantiate all modules.
     */
    public function instantiate(): self
    {
        $this->modules = collect([
            'top_level' => $this->instantiateModuleRecursively($this->config, 'top_level'),
        ]);

        return $this;
    }

    /**
     * Recursively instantiate module and its nested modules.
     */
    protected function instantiateModuleRecursively(Collection|array|string $config, string $key, ?string $moduleScope = null): InstallableModule
    {
        if ($imported = $config === '@import') {
            $config = $this->importModuleConfig($key);
        } elseif ($imported = $this->moduleConfigExists($key)) {
            $config = $this->importModuleConfig($key)->merge($config);
        }

        $moduleScope = $imported ? $key : $moduleScope;

        if ($options = Arr::get($config, 'options')) {
            $config['options'] = collect($options)
                ->map(fn ($optionConfig, $optionKey) => $this->instantiateModuleRecursively(
                    $optionConfig,
                    $this->normalizeModuleKey($key, $optionKey),
                    $moduleScope,
                ));
        }

        if ($modules = Arr::get($config, 'modules')) {
            $config['modules'] = collect($modules)
                ->map(fn ($childConfig, $childKey) => $this->instantiateModuleRecursively(
                    $childConfig,
                    $this->normalizeModuleKey($key, $childKey),
                    $moduleScope,
                ));
        }

        $module = (new InstallableModule($config, $key))->installer($this->installer);

        if ($moduleScope) {
            $this->scopeInstallableFiles($module, $moduleScope);
        }

        return $module;
    }

    /**
     * Import module config from modules folder.
     *
     * @throws StarterKitException
     */
    protected function importModuleConfig(string $key): Collection
    {
        $moduleConfig = $this->relativeModulePath($key, 'module.yaml');

        $absolutePath = $this->starterKitPath($moduleConfig);

        if (! $this->files->exists($absolutePath)) {
            throw new StarterKitException("Starter kit module config [$moduleConfig] does not exist.");
        }

        return collect(YAML::parse($this->files->get($absolutePath)));
    }

    /**
     * Ensure starter kit has config.
     *
     * @throws StarterKitException
     */
    protected function ensureModuleConfig(): self
    {
        if (! $this->files->exists($this->starterKitPath('starter-kit.yaml'))) {
            throw new StarterKitException('Starter kit config [starter-kit.yaml] does not exist.');
        }

        return $this;
    }

    /**
     * Normalize module key.
     */
    protected function normalizeModuleKey(string $key, string $childKey): string
    {
        return $key !== 'top_level' ? "{$key}.{$childKey}" : $childKey;
    }

    /**
     * Assemble absolute starter kit path.
     */
    protected function starterKitPath(?string $path = null): string
    {
        return collect([$this->starterKitPath, $path])->filter()->implode('/');
    }

    /**
     * Assemble relative imported module path.
     */
    protected function relativeModulePath(string $key, ?string $path = null): string
    {
        $base = 'modules/'.str_replace('.', '/', $key);

        return $path
            ? $base.Str::ensureLeft($path, '/')
            : $base;
    }

    /**
     * Determine whether module config exists.
     */
    protected function moduleConfigExists(string $key): bool
    {
        return $this->files->exists(
            $this->starterKitPath($this->relativeModulePath($key, 'module.yaml'))
        );
    }

    /**
     * Scope installable files to imported module.
     */
    protected function scopeInstallableFiles(InstallableModule $module, string $scope): void
    {
        $module->setRelativePath($this->relativeModulePath($scope));
    }

    /**
     * Flatten modules.
     */
    public static function flattenModules(Collection $modules): Collection
    {
        return $modules
            ->flatMap(function ($module) {
                return [
                    $module->key() => $module,
                    ...static::flattenModules($module->config('options', collect())),
                    ...static::flattenModules($module->config('modules', collect())),
                ];
            })
            ->filter();
    }
}
