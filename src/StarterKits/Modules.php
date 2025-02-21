<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Str;

abstract class Modules
{
    protected $config;
    protected $basePath;
    protected $files;
    protected $modules;

    /**
     * Instantiate modules helper.
     */
    public function __construct(Collection|array $config, string $basePath)
    {
        $this->config = collect($config);

        $this->basePath = $basePath;

        $this->files = app(Filesystem::class);
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
     * Get the preferences.
     *
     * @return array
     */
    abstract protected function instantiateIndividualModule(array|Collection $config, string $key): Module;

    /**
     * Recursively instantiate module and its nested modules.
     */
    protected function instantiateModuleRecursively(Collection|array|string $config, string $key, ?string $moduleScope = null): Module
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
                    $this->normalizeModuleKey($key, $this->prefixOptionsKey($optionKey)),
                    $moduleScope,
                ));
        }

        if ($modules = Arr::get($config, 'modules')) {
            $config['modules'] = collect($modules)
                ->map(fn ($childConfig, $childKey) => $this->instantiateModuleRecursively(
                    $childConfig,
                    $this->normalizeModuleKey($key, $this->prefixModulesKey($childKey)),
                    $moduleScope,
                ));
        }

        $module = $this->instantiateIndividualModule($config, $key);

        if ($moduleScope) {
            $this->scopeModulesPath($module, $moduleScope);
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

        $absolutePath = $this->basePath($moduleConfig);

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
        if (! $this->files->exists($this->basePath('starter-kit.yaml'))) {
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
     * Prefix options key.
     */
    protected function prefixOptionsKey(string $key): ?string
    {
        return 'options.'.$key;
    }

    /**
     * Prefix modules key.
     */
    protected function prefixModulesKey(string $key): ?string
    {
        return 'modules.'.$key;
    }

    /**
     * Assemble absolute path.
     */
    protected function basePath(?string $path = null): string
    {
        return collect([$this->basePath, $path])->filter()->implode('/');
    }

    /**
     * Assemble relative imported module path.
     */
    protected function relativeModulePath(string $key, ?string $path = null): string
    {
        $base = Str::ensureLeft(str_replace('.', '/', $key), 'modules/');

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
            $this->basePath($this->relativeModulePath($key, 'module.yaml'))
        );
    }

    /**
     * Scope modules path.
     */
    protected function scopeModulesPath(Module $module, string $scope): void
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
