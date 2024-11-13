<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Concerns\InteractsWithFilesystem;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Str;
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
            ->validatePackage()
            ->instantiateModules()
            ->clearExportPath()
            ->exportModules()
            ->exportConfig()
            ->exportHooks()
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
        if (! $this->files->exists(base_path('starter-kit.yaml'))) {
            throw new StarterKitException('Export config [starter-kit.yaml] does not exist.');
        }

        return $this;
    }

    /**
     * Validate package folder, if it exists.
     */
    protected function validatePackage(): self
    {
        if (! $this->files->exists(base_path('package'))) {
            return $this;
        }

        if (! $this->files->exists(base_path('package/composer.json'))) {
            throw new StarterKitException('Package config [package/composer.json] does not exist.');
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
    protected function instantiateModuleRecursively(array $config, string $key): ExportableModule|array
    {
        $instantiated = new ExportableModule($config, $key);

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
    protected function instantiateModule(array $config, string $key): ExportableModule|array
    {
        if (Arr::has($config, 'options') && $key !== 'top_level') {
            return $this->instantiateSelectModule($config, $key);
        }

        return $this->instantiateModuleRecursively($config, $key);
    }

    /**
     * Instantiate select module.
     */
    protected function instantiateSelectModule(array $config, string $key): ExportableModule|array
    {
        return collect($config['options'])
            ->map(fn ($option, $optionKey) => $this->instantiateModuleRecursively($option, "{$key}.options.{$optionKey}"))
            ->all();
    }

    /**
     * Normalize module key, as dotted array key for location in starter-kit.yaml.
     */
    protected function normalizeModuleKey(string $key, string $childKey): string
    {
        return $key !== 'top_level' ? "{$key}.modules.{$childKey}" : $childKey;
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
     * Export all the modules.
     */
    protected function exportModules(): self
    {
        $exportPath = $this->files->exists(base_path('package'))
            ? $this->exportPath.'/export'
            : $this->exportPath;

        $this->modules->each(fn ($module) => $module->export($exportPath));

        return $this;
    }

    /**
     * Get starter kit config.
     */
    protected function config(?string $key = null): mixed
    {
        $config = collect(YAML::parse($this->files->get(base_path('starter-kit.yaml'))));

        if ($key) {
            return $config->get($key);
        }

        return $config;
    }

    /**
     * Export starter kit config.
     */
    protected function exportConfig(): self
    {
        $config = $this
            ->versionModuleDependencies()
            ->syncConfigWithModules();

        $this->files->put("{$this->exportPath}/starter-kit.yaml", YAML::dump($config->all()));

        return $this;
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

        return 'modules.'.$module->key().'.'.$key;
    }

    /**
     * Export starter kit hooks.
     */
    protected function exportHooks(): self
    {
        $hooks = ['StarterKitPostInstall.php'];

        collect($hooks)
            ->filter(fn ($hook) => $this->files->exists(base_path($hook)))
            ->each(fn ($hook) => $this->exportRelativePath(
                from: $hook,
                starterKitPath: $this->exportPath,
            ));

        return $this;
    }

    /**
     * Export package config & other misc vendor files.
     */
    protected function exportPackage(): self
    {
        if (! $this->files->exists($packageFolder = base_path('package'))) {
            return $this->exportComposerJson();
        }

        $this->copyDirectoryContentsInto($packageFolder, $this->exportPath);

        return $this;
    }

    /**
     * Export composer.json.
     */
    protected function exportComposerJson(): self
    {
        $composerJson = $this->prepareComposerJsonFromStub()->all();

        $this->files->put(
            "{$this->exportPath}/composer.json",
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        return $this;
    }

    /**
     * Prepare composer.json from stub.
     */
    protected function prepareComposerJsonFromStub(): Collection
    {
        $stub = $this->getComposerJsonStub();

        $directory = preg_replace('/.*\/([^\/]*)/', '$1', $this->exportPath);
        $vendorName = $this->vendorName ?? 'my-vendor-name';
        $repoName = Str::slug($directory);
        $package = "{$vendorName}/{$repoName}";
        $title = Str::slugToTitle($repoName);

        $stub = str_replace('dummy/package', $package, $stub);
        $stub = str_replace('DummyTitle', $title, $stub);

        return collect(json_decode($stub, true));
    }

    /**
     * Get composer.json stub.
     */
    protected function getComposerJsonStub(): string
    {
        $stubPath = __DIR__.'/../Console/Commands/stubs/starter-kits/composer.json.stub';

        $existingComposerJsonPath = "{$this->exportPath}/composer.json";

        if ($this->files->exists($existingComposerJsonPath)) {
            return $this->files->get($existingComposerJsonPath);
        }

        return $this->files->get($stubPath);
    }
}
