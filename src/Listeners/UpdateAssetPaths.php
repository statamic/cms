<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetSaved;
use Statamic\Facades;

class UpdateAssetPaths implements ShouldQueue
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetSaved::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param AssetUploaded $event
     */
    public function handle($event)
    {
        $asset = $event->asset;

        $container = $asset->container()->handle();
        $originalPath = $asset->getOriginal('path');
        $newPath = $asset->path();

        if ($originalPath === $newPath) {
            return;
        }

        Facades\Entry::all()->each(function ($entry) use ($container, $originalPath, $newPath) {
            AssetReferenceUpdater::item($entry)->updateAssetReferences($container, $originalPath, $newPath);
        });

        Facades\Term::all()
            ->map->term()->flatMap->localizations() // https://github.com/statamic/cms/issues/3274
            ->each(function ($term) use ($container, $originalPath, $newPath) {
                AssetReferenceUpdater::item($term)->updateAssetReferences($container, $originalPath, $newPath);
            });

        Facades\GlobalSet::all()
            ->flatMap
            ->localizations()
            ->each(function ($globalSet) use ($container, $originalPath, $newPath) {
                AssetReferenceUpdater::item($globalSet)->updateAssetReferences($container, $originalPath, $newPath);
            });
    }
}
