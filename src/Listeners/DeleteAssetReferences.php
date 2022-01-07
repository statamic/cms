<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetDeleted;

class DeleteAssetReferences implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetDeleted::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  AssetDeleted  $event
     */
    public function handle(AssetDeleted $event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');

        $this->getItemsContainingData()->each(function ($item) use ($container, $originalPath) {
            AssetReferenceUpdater::item($item)
                ->filterByContainer($container)
                ->updateReferences($originalPath, null);
        });
    }
}
