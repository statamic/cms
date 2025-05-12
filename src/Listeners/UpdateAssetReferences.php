<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReferencesUpdated;
use Statamic\Events\AssetReplaced;
use Statamic\Events\AssetSaved;
use Statamic\Events\Subscriber;

class UpdateAssetReferences extends Subscriber implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

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
     * Replace asset references.
     *
     * @param  \Statamic\Assets\Asset  $asset
     * @param  string  $originalPath
     * @param  string  $newPath
     */
    protected function replaceReferences($asset, $originalPath, $newPath)
    {
        if (! $originalPath || $originalPath === $newPath) {
            return;
        }

        $container = $asset->container()->handle();

        $hasUpdatedItems = false;

        $this
            ->getItemsContainingData()
            ->each(function ($item) use ($container, $originalPath, $newPath, &$hasUpdatedItems) {
                $updated = AssetReferenceUpdater::item($item)
                    ->filterByContainer($container)
                    ->updateReferences($originalPath, $newPath);

                if ($updated) {
                    $hasUpdatedItems = true;
                }
            });

        if ($hasUpdatedItems) {
            AssetReferencesUpdated::dispatch($asset);
        }
    }
}
