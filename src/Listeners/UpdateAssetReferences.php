<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetSaved;

class UpdateAssetReferences implements ShouldQueue
{
    use Concerns\GetsItemsContainingData;

    /**
     * Get the name of the listener's queue connection.
     *
     * @return string
     */
    public function viaConnection()
    {
        if ($connection = config('statamic.system.queue_connection')) {
            return $connection;
        }

        return config('queue.default');
    }

    /**
     * Get the name of the listener's queue.
     *
     * @return string
     */
    public function viaQueue()
    {
        if ($queue = config('statamic.system.queue')) {
            return $queue;
        }

        return config('queue.connections.'.$this->viaConnection().'.queue');
    }

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
