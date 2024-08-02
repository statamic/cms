<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

class Exporter
{
    protected $files;
    protected $exportPath;
    protected $vendorName;

    /**
     * Instantiate starter kit exporter.
     */
    public function __construct()
    {
        $this->files = app(Filesystem::class);
    }

    /**
     * Export starter kit.
     *
     * @throws StarterKitException
     */
    public function export(string $absolutePath): void
    {
        $this->exportPath = $absolutePath;

        if (! $this->files->exists($this->exportPath)) {
            throw new StarterKitException("Path [$this->exportPath] does not exist.");
        }

        if (! $this->files->exists(base_path('starter-kit.yaml'))) {
            throw new StarterKitException('Export config [starter-kit.yaml] does not exist.');
        }

        $this
            ->exportFiles()
            ->exportConfig()
            ->exportHooks()
            ->exportComposerJson();
    }

    /**
     * Export files and folders.
     */
    protected function exportFiles(): self
    {
        $this
            ->exportPaths()
            ->each(function ($path) {
                $this->ensureExportPathExists($path);
            })
            ->each(function ($path) {
                $this->copyPath($path);
            });

        $this
            ->exportAsPaths()
            ->each(function ($to, $from) {
                $this->ensureExportPathExists($from);
            })
            ->each(function ($to, $from) {
                $this->copyPath($from, $to);
            });

        return $this;
    }

    /**
     * Ensure export path exists.
     *
     * @throws StarterKitException
     */
    protected function ensureExportPathExists(string $path)
    {
        if (! $this->files->exists(base_path($path))) {
            throw new StarterKitException("Export path [{$path}] does not exist.");
        }
    }

    /**
     * Copy path to new export path location.
     */
    protected function copyPath(string $fromPath, ?string $toPath = null): void
    {
        $toPath = $toPath
            ? "{$this->exportPath}/{$toPath}"
            : "{$this->exportPath}/{$fromPath}";

        $fromPath = base_path($fromPath);

        $this->preparePath($fromPath, $toPath);

        $this->files->isDirectory($fromPath)
            ? $this->files->copyDirectory($fromPath, $toPath)
            : $this->files->copy($fromPath, $toPath);
    }

    /**
     * Prepare path directory.
     */
    protected function preparePath(string $fromPath, string $toPath): void
    {
        $directory = $this->files->isDirectory($fromPath)
            ? $toPath
            : preg_replace('/(.*)\/[^\/]*/', '$1', $toPath);

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Get starter kit config.
     */
    protected function config(): Collection
    {
        return collect(YAML::parse($this->files->get(base_path('starter-kit.yaml'))));
    }

    /**
     * Get starter kit `export_paths` paths from config.
     *
     * @throws StarterKitException
     */
    protected function exportPaths(): Collection
    {
        $paths = collect($this->config()->get('export_paths'));

        if ($paths->isEmpty()) {
            throw new StarterKitException('Export config [starter-kit.yaml] does not contain any export paths.');
        } elseif ($paths->contains('composer.json')) {
            throw new StarterKitException('Cannot export [composer.json]. Please use `dependencies` array!');
        }

        return $paths;
    }

    /**
     * Get starter kit 'export_as' paths (to be renamed on export) from config.
     *
     * @throws StarterKitException
     */
    protected function exportAsPaths(): Collection
    {
        $paths = collect($this->config()->get('export_as'));

        if ($paths->keys()->contains('composer.json')) {
            throw new StarterKitException('Cannot export [composer.json]. Please use `dependencies` array!');
        }

        return $paths;
    }

    /**
     * Export starter kit config.
     */
    protected function exportConfig(): self
    {
        $config = $this->config();

        $config = $this->exportDependenciesFromComposerJson($config);

        $this->files->put("{$this->exportPath}/starter-kit.yaml", YAML::dump($config->all()));

        return $this;
    }

    /**
     * Export starter kit hooks.
     */
    protected function exportHooks(): self
    {
        $hooks = ['StarterKitPostInstall.php'];

        collect($hooks)
            ->filter(fn ($hook) => $this->files->exists(base_path($hook)))
            ->each(fn ($hook) => $this->copyPath($hook));

        return $this;
    }

    /**
     * Export dependencies from composer.json.
     */
    protected function exportDependenciesFromComposerJson(Collection $config): Collection
    {
        $exportableDependencies = $this->getExportableDependenciesFromConfig($config);

        $config
            ->forget('dependencies')
            ->forget('dependenices_dev');

        if ($dependencies = $this->exportDependenciesFromComposerRequire('require', $exportableDependencies)) {
            $config->put('dependencies', $dependencies->all());
        }

        if ($devDependencies = $this->exportDependenciesFromComposerRequire('require-dev', $exportableDependencies)) {
            $config->put('dependencies_dev', $devDependencies->all());
        }

        return $config;
    }

    /**
     * Get exportable dependencies without versions from config.
     */
    protected function getExportableDependenciesFromConfig(Collection $config): Collection
    {
        if ($this->hasDependenciesWithoutVersions($config)) {
            return collect($config->get('dependencies') ?? []);
        }

        return collect()
            ->merge($config->get('dependencies') ?? [])
            ->merge($config->get('dependencies_dev') ?? [])
            ->keys();
    }

    /**
     * Check if config has dependencies without versions.
     */
    protected function hasDependenciesWithoutVersions(Collection $config): bool
    {
        if (! $config->has('dependencies')) {
            return false;
        }

        return isset($config['dependencies'][0]);
    }

    /**
     * Export dependencies from composer.json using specific require key.
     */
    protected function exportDependenciesFromComposerRequire(string $requireKey, Collection $exportableDependencies): mixed
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $dependencies = collect($composerJson[$requireKey] ?? [])
            ->filter(function ($version, $dependency) use ($exportableDependencies) {
                return $exportableDependencies->contains($dependency);
            });

        return $dependencies->isNotEmpty()
            ? $dependencies
            : false;
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
