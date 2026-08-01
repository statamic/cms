<?php

namespace Statamic\Sidecar;

use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\Collection;
use Statamic\Fields\Blueprint;

/**
 * Contract for Sidecar collection adapter drivers.
 *
 * Drivers teach Statamic how to edit content belonging to an external
 * static site generator (or similar markdown-based system) in place.
 *
 * @experimental
 */
interface Driver
{
    /**
     * Human-readable title for the collection.
     */
    public function title(): string;

    /**
     * Absolute or base_path-relative directory containing the entries.
     */
    public function directory(): string;

    /**
     * Optional custom entry class for filename/serialization conventions.
     *
     * @return class-string<\Statamic\Contracts\Entries\Entry>|null
     */
    public function entryClass(): ?string;

    /**
     * Blueprint used when the collection has no blueprint files on disk.
     */
    public function blueprint(): Blueprint;

    /**
     * Customize the collection after it's been instantiated from config.
     */
    public function configure(Collection $collection): Collection;

    /**
     * Called after the collection has been registered during Sidecar boot.
     */
    public function afterBoot(Collection $collection): void;

    /**
     * Called after a sidecar-managed entry is saved.
     */
    public function afterSave(Entry $entry): void;

    /**
     * Called after a sidecar-managed entry is deleted.
     */
    public function afterDelete(Entry $entry): void;

    /**
     * Public URL for the entry (Visit URL + fallback Live Preview target).
     */
    public function previewUrl(Entry $entry): ?string;
}
