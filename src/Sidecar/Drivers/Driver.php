<?php

namespace Statamic\Sidecar\Drivers;

use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\Collection;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as BlueprintInstance;
use Statamic\Sidecar\Driver as DriverContract;
use Statamic\Support\Str;

/**
 * @experimental
 */
abstract class Driver implements DriverContract
{
    public function __construct(
        protected array $config,
        protected string $collectionHandle,
    ) {
    }

    abstract public function title(): string;

    public function directory(): string
    {
        return $this->config['directory']
            ?? throw new \InvalidArgumentException("Sidecar collection [{$this->collectionHandle}] is missing a directory.");
    }

    public function entryClass(): ?string
    {
        return $this->config['entry_class'] ?? null;
    }

    public function blueprint(): BlueprintInstance
    {
        if ($handle = $this->config['blueprint'] ?? null) {
            return Blueprint::find($handle)
                ?? throw new \InvalidArgumentException("Sidecar blueprint [{$handle}] not found.");
        }

        return $this->defaultBlueprint();
    }

    abstract protected function defaultBlueprint(): BlueprintInstance;

    public function configure(Collection $collection): Collection
    {
        return $collection
            ->title($this->config['title'] ?? $this->title())
            ->directory($this->directory())
            ->entryClass($this->entryClass())
            // Store a Blueprint instance (not a Closure) so Stache can serialize
            // the collection when SEO Pro / CP saves collection cascade data.
            ->entryBlueprintFallback($this->blueprint())
            ->routes(null)
            ->requiresSlugs(true);
    }

    public function afterBoot(Collection $collection): void
    {
        //
    }

    public function afterSave(Entry $entry): void
    {
        //
    }

    public function afterDelete(Entry $entry): void
    {
        //
    }

    public function previewUrl(Entry $entry): ?string
    {
        return null;
    }

    public function collectionHandle(): string
    {
        return $this->collectionHandle;
    }

    protected function makeBlueprint(array $contents): BlueprintInstance
    {
        return Blueprint::make(Str::singular($this->collectionHandle))
            ->setNamespace('collections.'.$this->collectionHandle)
            ->setContents($contents);
    }
}
