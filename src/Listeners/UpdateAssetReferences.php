<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
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
        $events->listen(AssetSaved::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  AssetSaved  $event
     */
    public function handle(AssetSaved $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

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
