<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Concerns\InteractsWithFilesystem;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Exporter
{
    use InteractsWithFilesystem;

    protected $exportPath;
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
            ->exportModules()
            ->exportConfig()
            ->exportHooks()
            ->exportComposerJson();
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
     * Instantiate and validate modules that are to be installed.
     */
    protected function instantiateModules(): self
    {
        $topLevelConfig = $this->config()->all();

        $nestedConfigs = $this->config('modules');

        $this->modules = collect(['top_level' => $topLevelConfig])
            ->merge($nestedConfigs)
            ->map(fn ($config, $key) => $this->instantiateModule($config, $key))
            ->flatten()
            ->filter()
            ->each(fn ($module) => $module->validate());

        return $this;
    }

    /**
     * Instantiate individual module.
     */
    protected function instantiateModule(array $config, string $key): ExportableModule|array
    {
        if (Arr::has($config, 'options')) {
            return collect($config['options'])
                ->map(fn ($option) => $this->instantiateModule($option, $key))
                ->all();
        }

        return new ExportableModule($config, $key);
    }

    /**
     * Export all the modules.
     */
    protected function exportModules(): self
    {
        $this->modules->each(fn ($module) => $module->export($this->exportPath));

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
            ->syncWithModuleConfigs();

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
    protected function syncWithModuleConfigs(): Collection
    {
        $config = $this->config()->all();

        $this->modules->each(function ($module) use (&$config) {
            if ($dependencies = $module->config('dependencies')) {
                Arr::forget($config, $this->dottedModulePath($module, 'dependencies'));
                Arr::set($config, $this->dottedModulePath($module, 'dependencies'), $dependencies);
            }
        });

        $this->modules->each(function ($module) use (&$config) {
            if ($dependenciesDev = $module->config('dependencies_dev')) {
                Arr::forget($config, $this->dottedModulePath($module, 'dependencies_dev'));
                Arr::set($config, $this->dottedModulePath($module, 'dependencies_dev'), $dependenciesDev);
            }
        });

        return collect($config);
    }

    /**
     * Get dotted module path.
     */
    protected function dottedModulePath(Module $module, string $key): string
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
            ->each(fn ($hook) => $this->exportPath(
                from: $hook,
                starterKitPath: $this->exportPath,
            ));

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
