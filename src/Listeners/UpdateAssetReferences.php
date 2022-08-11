<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetReplaced;
use Statamic\Events\AssetSaved;

class UpdateAssetReferences implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        if (config('statamic.assets.update_references') === false) {
            return;
        }

        $events->listen(AssetSaved::class, self::class.'@handleSaved');
        $events->listen(AssetReplaced::class, self::class.'@handleReplaced');
    }

    /**
     * Handle the asset saved event.
     *
     * @param  AssetSaved  $event
     */
    public function handleSaved(AssetSaved $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

        $this->replaceReferences($container, $originalPath, $newPath);
    }

    /**
     * Handle the asset replaced event.
     *
     * @param  AssetReplaced  $event
     */
    public function handleReplaced(AssetReplaced $event)
    {
        $container = $event->newAsset->container()->handle();
        $originalPath = $event->originalAsset->path();
        $newPath = $event->newAsset->path();

        $this->replaceReferences($container, $originalPath, $newPath);
    }

    /**
     * Replace asset references.
     *
     * @param  string  $container
     * @param  string  $originalPath
     * @param  string  $newPath
     */
    protected function replaceReferences($container, $originalPath, $newPath)
    {
        if (! $originalPath || $originalPath === $newPath) {
            return;
        }

        $this->getItemsContainingData()->each(function ($item) use ($container, $originalPath, $newPath) {
            AssetReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->updateReferences($originalPath, $newPath);
        });
    }
}
