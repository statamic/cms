<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
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
     * @param  string  $absolutePath
     *
     * @throws StarterKitException
     */
    public function export($absolutePath)
    {
        $this->exportPath = $absolutePath;

        if (! $this->files->exists($this->exportPath)) {
            throw new StarterKitException("Path [$exportPath] does not exist.");
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
     *
     * @return $this
     */
    protected function exportFiles()
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
     * @param  string  $path
     *
     * @throws StarterKitException
     */
    protected function ensureExportPathExists($path)
    {
        if (! $this->files->exists(base_path($path))) {
            throw new StarterKitException("Export path [{$path}] does not exist.");
        }
    }

    /**
     * Copy path to new export path location.
     *
     * @param  string  $fromPath
     * @param  string  $toPath
     */
    protected function copyPath($fromPath, $toPath = null)
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
     *
     * @param  string  $fromPath
     * @param  string  $toPath
     */
    protected function preparePath($fromPath, $toPath)
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
     *
     * @return \Illuminate\Support\Collection
     */
    protected function config()
    {
        return collect(YAML::parse($this->files->get(base_path('starter-kit.yaml'))));
    }

    /**
     * Get starter kit `export_paths` paths from config.
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws StarterKitException
     */
    protected function exportPaths()
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
     * @return \Illuminate\Support\Collection
     *
     * @throws StarterKitException
     */
    protected function exportAsPaths()
    {
        $paths = collect($this->config()->get('export_as'));

        if ($paths->keys()->contains('composer.json')) {
            throw new StarterKitException('Cannot export [composer.json]. Please use `dependencies` array!');
        }

        return $paths;
    }

    /**
     * Export starter kit config.
     *
     * @return $this
     */
    protected function exportConfig()
    {
        $config = $this->config();

        $config = $this->exportDependenciesFromComposerJson($config);

        $this->files->put("{$this->exportPath}/starter-kit.yaml", YAML::dump($config->all()));

        return $this;
    }

    /**
     * Export starter kit hooks.
     *
     * @return $this
     */
    protected function exportHooks()
    {
        $hooks = ['StarterKitPostInstall.php'];

        collect($hooks)
            ->filter(fn ($hook) => $this->files->exists(base_path($hook)))
            ->each(fn ($hook) => $this->copyPath($hook));

        return $this;
    }

    /**
     * Export dependencies from composer.json.
     *
     * @param  \Illuminate\Support\Collection  $config
     * @return \Illuminate\Support\Collection
     */
    protected function exportDependenciesFromComposerJson($config)
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
     *
     * @param  \Illuminate\Support\Collection  $config
     * @return \Illuminate\Support\Collection
     */
    protected function getExportableDependenciesFromConfig($config)
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
     *
     * @param  \Illuminate\Support\Collection  $config
     * @return bool
     */
    protected function hasDependenciesWithoutVersions($config)
    {
        if (! $config->has('dependencies')) {
            return false;
        }

        return isset($config['dependencies'][0]);
    }

    /**
     * Export dependencies from composer.json using specific require key.
     *
     * @param  string  $requireKey
     * @param  \Illuminate\Support\Collection  $exportableDependencies
     * @return \Illuminate\Support\Collection
     */
    protected function exportDependenciesFromComposerRequire($requireKey, $exportableDependencies)
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
     *
     * @return $this
     */
    protected function exportComposerJson()
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
     *
     * @return \Illuminate\Support\Collection
     */
    protected function prepareComposerJsonFromStub()
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
     *
     * @return string
     */
    protected function getComposerJsonStub()
    {
        $stubPath = __DIR__.'/../Console/Commands/stubs/starter-kits/composer.json.stub';

        $existingComposerJsonPath = "{$this->exportPath}/composer.json";

        if ($this->files->exists($existingComposerJsonPath)) {
            return $this->files->get($existingComposerJsonPath);
        }

        return $this->files->get($stubPath);
    }
}
