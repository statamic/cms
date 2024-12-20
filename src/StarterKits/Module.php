<?php

namespace Statamic\StarterKits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\StarterKits\Exceptions\StarterKitException;

abstract class Module
{
    use Concerns\InteractsWithFilesystem;

    protected $files;
    protected $config;
    protected $key;
    protected $relativePath;

    /**
     * Instantiate starter kit module.
     */
    public function __construct(array|Collection $config, string $key)
    {
        $this->files = app(Filesystem::class);

        $this->config = collect($config);

        $this->key = $key;
    }

    /**
     * Set relative module path.
     */
    public function setRelativePath(string $path): self
    {
        $this->relativePath = $path;

        return $this;
    }

    /**
     * Get module key.
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Get readable module key for default prompt display text.
     */
    public function keyReadable(): string
    {
        return str_replace(['_', '.'], ' ', $this->key);
    }

    /**
     * Check if this is a top level module.
     */
    public function isTopLevelModule(): bool
    {
        return $this->key === 'top_level';
    }

    /**
     * Set config.
     */
    public function set(string $key, mixed $value): self
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Get module config.
     */
    public function config(?string $key = null, $default = null): mixed
    {
        if ($key) {
            return $this->config->get($key, $default);
        }

        return $this->config;
    }

    /**
     * Get `export_paths` paths as collection from config.
     */
    protected function exportPaths(): Collection
    {
        return collect($this->config('export_paths') ?? []);
    }

    /**
     * Get `export_as` paths (to be renamed on install) as collection from config.
     *
     * This is only here for backwards compatibility. Use new `export` folder convention instead.
     *
     * @deprecated
     */
    protected function exportAsPaths(): Collection
    {
        return collect($this->config('export_as') ?? []);
    }

    /**
     * Ensure nested module config is not empty.
     *
     * @throws StarterKitException
     */
    protected function ensureModuleConfigNotEmpty(): static
    {
        $hasConfig = $this->config()->has('export_paths')
            || $this->config()->has('export_as')
            || $this->config()->has('dependencies')
            || $this->config()->has('dependencies_dev')
            || $this->config()->has('options')
            || $this->config()->has('modules');

        if (! $hasConfig) {
            throw new StarterKitException('Starter-kit module is missing `export_paths`, `dependencies`, or nested `modules`.');
        }

        return $this;
    }
}
