<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Concerns\InteractsWithFilesystem;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Exporter
{
    use FluentlyGetsAndSets, InteractsWithFilesystem;

    protected $exportPath;
    protected $clear;
    protected $files;
    protected $vendorName;
    protected $modules;

    /**
     * Instantiate starter kit exporter.
     */
    public function __construct(string $exportPath)
    {
        $this->exportPath = $exportPath;

        $this->files = app(Filesystem::class);
    }

    /**
     * Get or set whether to clear out everything at target export path before exporting.
     */
    public function clear(bool $clear = false): self|bool|null
    {
        return $this->fluentlyGetOrSet('clear')->args(func_get_args());
    }

    /**
     * Export starter kit.
     *
     * @throws StarterKitException
     */
    public function export(): void
    {
        $this
            ->validateExportPath()
            ->validateConfig()
            ->instantiateModules()
            ->clearExportPath()
            ->exportModules()
            ->exportPackage();
    }

    /**
     * Validate that export path exists.
     */
    protected function validateExportPath(): self
    {
        if (! $this->files->exists($this->exportPath)) {
            throw new StarterKitException("Path [$this->exportPath] does not exist.");
        }

        return $this;
    }

    /**
     * Validate starter kit config.
     */
    protected function validateConfig(): self
    {
        if (! $this->files->exists(base_path('package/starter-kit.yaml'))) {
            throw new StarterKitException('Starter kit config [package/starter-kit.yaml] does not exist.');
        }

        if (! $this->files->exists(base_path('package/composer.json'))) {
            throw new StarterKitException('Package config [package/composer.json] does not exist.');
        }

        return $this;
    }

    /**
     * Instantiate and prepare flattened modules that are to be exported.
     */
    protected function instantiateModules(): self
    {
        ray('instantiating')->purple();
        $this->modules = (new ExportableModules($this->config(), $this->exportPath))
            ->instantiate()
            ->all()
            ->pipe(fn ($module) => ExportableModules::flattenModules($module))
            ->each(fn ($module) => $module->validate());
        ray('instantiated')->purple();

        ray($this->modules)->purple();

        return $this;
    }

    /**
     * Optionally clear out everything at target export path before exporting.
     */
    protected function clearExportPath()
    {
        if (! $this->clear) {
            return $this;
        }

        $this->files->cleanDirectory($this->exportPath);

        return $this;
    }

    /**
     * Export all inline modules.
     */
    protected function exportModules(): self
    {
        $this->modules->each(fn ($module) => $module->export($this->exportPath.'/export'));

        return $this;
    }

    /**
     * Get starter kit config.
     */
    protected function config(?string $key = null): mixed
    {
        $config = collect(YAML::parse($this->files->get(base_path('package/starter-kit.yaml'))));

        if ($key) {
            return $config->get($key);
        }

        return $config;
    }

    /**
     * Version module dependencies from composer.json.
     */
    protected function versionModuleDependencies(): self
    {
        $this->modules->map(fn ($module) => $module->versionDependencies());

        return $this;
    }

    /**
     * Get synced config from newly versioned module dependencies.
     */
    protected function syncConfigWithModules(): Collection
    {
        $config = $this->config()->all();

        $normalizedModuleKeyOrder = [
            'prompt',
            'label',
            'skip_option',
            'options',
            'export_paths',
            'export_as',
            'dependencies',
            'dependencies_dev',
            'modules',
        ];

        $this->modules->each(function ($module) use ($normalizedModuleKeyOrder, &$config) {
            foreach ($normalizedModuleKeyOrder as $key) {
                $this->syncConfigWithIndividualModule($config, $module, $key);
            }
        });

        return collect($config);
    }

    /**
     * Sync config with individual module
     */
    protected function syncConfigWithIndividualModule(array &$config, ExportableModule $module, string $key)
    {
        Arr::forget($config, $this->dottedModulePath($module, $key));

        if ($module->config()->has($key)) {
            Arr::set($config, $this->dottedModulePath($module, $key), $module->config($key));
        }
    }

    /**
     * Get dotted module path.
     */
    protected function dottedModulePath(ExportableModule $module, string $key): string
    {
        if ($module->isTopLevelModule()) {
            return $key;
        }

        return $module->key().'.'.$key;
    }

    /**
     * Export package config & other misc vendor files.
     */
    protected function exportPackage(): self
    {
        $this->copyDirectoryContentsInto(base_path('package'), $this->exportPath);

        $config = $this
            ->versionModuleDependencies()
            ->syncConfigWithModules();

        $this->files->put("{$this->exportPath}/starter-kit.yaml", YAML::dump($config->all()));

        return $this;
    }
}
