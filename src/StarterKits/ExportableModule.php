<?php

namespace Statamic\StarterKits;

use Exception;
use Illuminate\Support\Collection;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Str;

class ExportableModule extends Module
{
    /**
     * Validate starter kit module is exportable.
     *
     * @throws Exception|StarterKitException
     */
    public function validate(): void
    {
        $this
            ->ensureModuleConfigNotEmpty()
            ->ensureNotExportingComposerJson()
            ->ensureExportablePathsExist()
            ->ensureExportableDependenciesExist();
    }

    /**
     * Export starter kit module.
     *
     * @throws Exception|StarterKitException
     */
    public function export(string $starterKitPath): void
    {
        $this
            ->exportPaths()
            ->each(fn ($path) => $this->exportRelativePath(
                from: $path,
                starterKitPath: $starterKitPath,
            ));

        $this
            ->exportAsPaths()
            ->each(fn ($to, $from) => $this->exportRelativePath(
                from: $from,
                to: $to,
                starterKitPath: $starterKitPath,
            ));
    }

    public function versionDependencies(): self
    {
        $exportableDependencies = $this->exportableDependencies();

        $this->config->forget('dependencies');
        $this->config->forget('dependencies_dev');

        if ($dependencies = $this->exportDependenciesFromComposerRequire('require', $exportableDependencies)) {
            $this->config->put('dependencies', $dependencies->all());
        }

        if ($devDependencies = $this->exportDependenciesFromComposerRequire('require-dev', $exportableDependencies)) {
            $this->config->put('dependencies_dev', $devDependencies->all());
        }

        return $this;
    }

    /**
     * Get exportable dependencies without versions from module config.
     */
    protected function exportableDependencies(): Collection
    {
        $config = $this->config();

        return collect()
            ->merge($config->get('dependencies') ?? [])
            ->merge($config->get('dependencies_dev') ?? [])
            ->map(function ($value, $key) {
                return Str::contains($key, '/')
                    ? $key
                    : $value;
            });
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
     * Ensure composer.json is not one of the export paths.
     *
     * @throws StarterKitException
     */
    protected function ensureNotExportingComposerJson(): self
    {
        // Here we'll ensure both `export_as` values and keys are included,
        // because we want to make sure `composer.json` is referenced on either end.
        $flattenedExportPaths = $this
            ->exportPaths()
            ->merge($this->exportAsPaths())
            ->merge($this->exportAsPaths()->keys());

        if ($flattenedExportPaths->contains('starter-kit.yaml')) {
            throw new StarterKitException('Cannot export [starter-kit.yaml] config.');
        }

        if ($flattenedExportPaths->contains('composer.json')) {
            throw new StarterKitException('Cannot export [composer.json]. Please use `dependencies` array.');
        }

        return $this;
    }

    /**
     * Ensure export paths exist.
     *
     * @throws StarterKitException
     */
    protected function ensureExportablePathsExist(): self
    {
        $this
            ->exportPaths()
            ->merge($this->exportAsPaths()->keys())
            ->reject(fn ($path) => $this->files->exists(base_path($path)))
            ->each(function ($path) {
                throw new StarterKitException("Cannot export [{$path}], because it does not exist in your app.");
            });

        return $this;
    }

    /**
     * Ensure export dependencies exist in app's composer.json.
     *
     * @throws StarterKitException
     */
    protected function ensureExportableDependenciesExist(): self
    {
        $installedDependencies = collect(json_decode($this->files->get(base_path('composer.json')), true))
            ->only(['require', 'require-dev'])
            ->map(fn ($dependencies) => array_keys($dependencies))
            ->flatten();

        $this
            ->exportableDependencies()
            ->reject(fn ($dependency) => $installedDependencies->contains($dependency))
            ->each(function ($dependency) {
                throw new StarterKitException("Cannot export [{$dependency}], because it does not exist in your composer.json.");
            });

        return $this;
    }
}
