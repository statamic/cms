<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReferencesUpdated;
use Statamic\Events\AssetReplaced;
use Statamic\Events\AssetSaved;
use Statamic\Events\Subscriber;
use Statamic\Facades\Asset;
use Statamic\Git\Subscriber as GitSubscriber;
use Statamic\Jobs\UpdateBatchedAssetReferences;

class UpdateAssetReferences extends Subscriber implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    /**
     * The most asset moves handled by a single job, which scans all the content once.
     *
     * @var int
     */
    public static $batchChunkSize = 500;

    /**
     * The asset moves recorded by the batch in progress, grouped by container handle, in the
     * order they happened. A null new path means the asset was deleted.
     *
     * @var array<string, array<int, array{0: string, 1: string|null}>>|null
     */
    protected static $batch = null;

    protected $listeners = [
        AssetSaved::class => 'handleSaved',
        AssetReplaced::class => 'handleReplaced',
        AssetDeleted::class => 'handleDeleted',
    ];

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        if (config('statamic.system.update_references') === false) {
            return;
        }

        parent::subscribe($events);
    }

    /**
     * Run a callback that moves or deletes many assets, updating their references together.
     *
     * Every reference update scans all the content on the site. Rather than queueing one
     * update per asset, the moves made inside the callback are collected and handled by
     * a single job (or one per chunk) once the callback has finished.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function batch(callable $callback)
    {
        if (static::$batch !== null) {
            return $callback();
        }

        static::$batch = [];

        try {
            return $callback();
        } finally {
            $batch = static::$batch;
            static::$batch = null;

            // Dispatched even if the callback failed partway, so the assets that did move keep their references.
            static::dispatchBatch($batch);
        }
    }

    /**
     * Queue the jobs that update the references for a finished batch.
     *
     * @param  array<string, array<int, array{0: string, 1: string|null}>>  $batch
     */
    protected static function dispatchBatch(array $batch): void
    {
        $jobs = collect($batch)
            ->flatMap(fn ($moves, $container) => collect($moves)
                ->chunk(static::$batchChunkSize)
                ->map(fn ($chunk) => new UpdateBatchedAssetReferences($container, $chunk->values()->all())))
            ->values();

        if ($jobs->isEmpty()) {
            return;
        }

        // Chained so the chunks run one after another, in the order the assets were moved.
        Bus::chain($jobs->all())->dispatch();
    }

    /**
     * Determine whether a reference update should be queued for the event.
     *
     * Laravel calls this when the event is dispatched. While a batch is in progress,
     * saved and deleted assets are added to the batch instead of being queued.
     */
    public function shouldQueue($event): bool
    {
        if (static::$batch === null) {
            return true;
        }

        if ($event instanceof AssetSaved) {
            $asset = $event->asset;
            $newPath = $asset->path();
        } elseif ($event instanceof AssetDeleted) {
            $asset = $event->asset;
            $newPath = null;
        } else {
            return true;
        }

        $originalPath = $asset->getOriginal('path');

        if ($originalPath && $originalPath !== $newPath) {
            static::$batch[$asset->container()->handle()][] = [$originalPath, $newPath];
        }

        return false;
    }

    /**
     * Handle the asset saved event.
     */
    public function handleSaved(AssetSaved $event)
    {
        $asset = $event->asset;
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

        $this->replaceReferences($asset, $originalPath, $newPath);
    }

    /**
     * Handle the asset replaced event.
     */
    public function handleReplaced(AssetReplaced $event)
    {
        $asset = $event->newAsset;
        $originalPath = $event->originalAsset->path();
        $newPath = $event->newAsset->path();

        $this->replaceReferences($asset, $originalPath, $newPath);
    }

    /**
     * Handle the asset deleted event.
     */
    public function handleDeleted(AssetDeleted $event)
    {
        $asset = $event->asset;
        $originalPath = $asset->getOriginal('path');
        $newPath = null;

        $this->replaceReferences($asset, $originalPath, $newPath);
    }

    /**
     * Update the references for the asset moves collected by a batch.
     *
     * @param  array<int, array{0: string, 1: string|null}>  $moves  Original path and new path (null when deleted), in the order they happened.
     */
    public function handleBatch(string $container, array $moves)
    {
        foreach ($this->replaceManyReferences($container, $moves) as [$originalPath, $newPath]) {
            // A deleted asset, or one that's been moved again since, gets a stand-in for the event.
            $asset = ($newPath ? Asset::find($container.'::'.$newPath) : null)
                ?? Asset::make()->container($container)->path($newPath ?? $originalPath);

            AssetReferencesUpdated::dispatch($asset);
        }
    }

    /**
     * Replace asset references.
     *
     * @param  \Statamic\Assets\Asset  $asset
     * @param  string  $originalPath
     * @param  string  $newPath
     */
    protected function replaceReferences($asset, $originalPath, $newPath)
    {
        if ($this->replaceManyReferences($asset->container()->handle(), [[$originalPath, $newPath]])) {
            AssetReferencesUpdated::dispatch($asset);
        }
    }

    /**
     * Replace the references for many asset moves in a single pass over the content.
     *
     * The moves are applied to each item in order, so a path can be moved more than once
     * (or two assets can swap paths) and each item still ends up with the right references.
     *
     * @param  array<int, array{0: string, 1: string|null}>  $moves  Original path and new path (null when removing).
     * @return array<int, array{0: string, 1: string|null}> The moves that updated at least one item.
     */
    protected function replaceManyReferences(string $container, array $moves): array
    {
        $moves = array_filter($moves, fn ($move) => $move[0] && $move[0] !== $move[1]);

        if (! $moves) {
            return [];
        }

        $updatedMoves = [];

        $this
            ->getItemsContainingData()
            ->each(function ($item) use ($container, $moves, &$updatedMoves) {
                if ($this->updateItemReferences($item, $container, $moves, $updatedMoves)) {
                    GitSubscriber::withoutListeners(fn () => $item->save());
                }

                if (method_exists($item, 'hasWorkingCopy') && $item->hasWorkingCopy()) {
                    $workingCopyEntry = $item->makeFromRevision($item->workingCopy());

                    // Only updates to saved items count towards the AssetReferencesUpdated events.
                    $workingCopyUpdatedMoves = [];

                    if ($this->updateItemReferences($workingCopyEntry, $container, $moves, $workingCopyUpdatedMoves)) {
                        $workingCopyEntry->saveToWorkingCopy();
                    }
                }
            });

        ksort($updatedMoves);

        return array_values($updatedMoves);
    }

    /**
     * Apply each move to an item, in order, without saving it.
     *
     * @return bool Whether the item was updated.
     */
    private function updateItemReferences($item, string $container, array $moves, array &$updatedMoves): bool
    {
        $updated = false;

        // Encode the item's data once rather than once per move, re-encoding only after it changes,
        // since an earlier move can introduce a path that a later move then replaces.
        $encodedData = AssetReferenceUpdater::encodeData($item);

        foreach ($moves as $index => [$originalPath, $newPath]) {
            if (! AssetReferenceUpdater::encodedDataMayContain($encodedData, $originalPath)) {
                continue;
            }

            $itemUpdated = AssetReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->withoutSaving()
                ->updateReferences($originalPath, $newPath);

            if ($itemUpdated) {
                $updated = true;
                $updatedMoves[$index] = [$originalPath, $newPath];
                $encodedData = AssetReferenceUpdater::encodeData($item);
            }
        }

        return $updated;
    }
}
