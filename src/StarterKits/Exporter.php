<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\YAML;
use Statamic\StarterKits\Exceptions\StarterKitException;

class Exporter
{
    protected $files;
    protected $exportPath;

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
     * @param string $exportPath
     * @throws StarterKitException
     */
    public function export($exportPath)
    {
        if (! $this->files->exists(base_path($exportPath))) {
            throw new StarterKitException("Path [$exportPath] does not exist.");
        }

        if (! $this->files->exists(base_path('starter-kit.yaml'))) {
            throw new StarterKitException("Export config [starter-kit.yaml] does not exist.");
        }

        $this->exportPath = $exportPath;

        $this
            ->exportFiles()
            ->exportDependencies()
            ->exportConfig();
    }

    /**
     * Export files and folders.
     *
     * @return $this
     */
    protected function exportFiles()
    {
        $this->exportPaths()
            ->each(function ($path) {
                $this->ensurePathExists($path);
            })
            ->each(function ($path) {
                $this->exportPath($path, base_path("{$this->exportPath}/{$path}"));
            });

        return $this;
    }

    /**
     * Ensure path exists.
     *
     * @param string $path
     * @throws StarterKitException
     */
    protected function ensurePathExists($path)
    {
        if (! $this->files->exists(base_path($path))) {
            throw new StarterKitException("Export path [{$path}] does not exist.");
        }
    }

    /**
     * Export path.
     *
     * @param string $fromPath
     * @param string $toPath
     */
    protected function exportPath($fromPath, $toPath)
    {
        $fromPath = base_path($fromPath);

        $this->preparePath($fromPath, $toPath);

        $this->files->isDirectory($fromPath)
            ? $this->files->copyDirectory($fromPath, $toPath)
            : $this->files->copy($fromPath, $toPath);
    }

    /**
     * Prepare path directory.
     *
     * @param string $fromPath
     * @param string $toPath
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
     * Get starter kit export paths from config.
     *
     * @return \Illuminate\Support\Collection
     * @throws StarterKitException
     */
    protected function exportPaths()
    {
        $paths = collect($this->config()->get('export_paths'));

        if ($paths->isEmpty()) {
            throw new StarterKitException("Export config [starter-kit.yaml] does not contain any export paths.");
        } elseif ($paths->contains('composer.json')) {
            throw new StarterKitException("Cannot export [composer.json]. Please use `dependencies` array!");
        }

        return $paths;
    }

    /**
     * Export starter kit dependencies.
     *
     * @return $this
     */
    protected function exportDependencies()
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $originalRequire = $this->getExportableDependencies($composerJson, 'require');
        $originalRequireDev = $this->getExportableDependencies($composerJson, 'require-dev');

        $exportComposerJson = [];

        if ($originalRequire->isNotEmpty()) {
            $exportComposerJson['require'] = $originalRequire->all();
        }

        if ($originalRequireDev->isNotEmpty()) {
            $exportComposerJson['require-dev'] = $originalRequireDev->all();
        }

        if ($exportComposerJson) {
            $this->files->put(
                base_path("{$this->exportPath}/composer.json"),
                json_encode($exportComposerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        return $this;
    }

    /**
     * Get exportable dependencies from appropriate require key in composer.json.
     *
     * @param array $composerJson
     * @param string $requireKey
     * @return \Illuminate\Support\Collection
     */
    protected function getExportableDependencies($composerJson, $requireKey)
    {
        return collect($composerJson[$requireKey] ?? [])->filter(function ($version, $package) {
            return $this->dependencies()->contains($package);
        });
    }

    /**
     * Get starter kit dependencies that should be copied from the composer.json.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function dependencies()
    {
        return collect($this->config()->get('dependencies'));
    }

    /**
     * Export starter kit config.
     *
     * @return $this
     */
    protected function exportConfig()
    {
        $this->files->copy(base_path('starter-kit.yaml'), base_path("{$this->exportPath}/starter-kit.yaml"));

        return $this;
    }
}
