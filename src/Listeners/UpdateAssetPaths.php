<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Assets\AssetReferenceUpdater;
use Statamic\Events\AssetSaved;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Term;
use Statamic\Facades\User;

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
     * @param AssetSaved $event
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

        collect()
            ->merge(Entry::all())
            ->merge(Term::all()->map->term()->flatMap->localizations()) // See issue #3274
            ->merge(GlobalSet::all()->flatMap->localizations())
            ->merge(User::all())
            ->each(function ($item) use ($container, $originalPath, $newPath) {
                AssetReferenceUpdater::item($item)->updateAssetReferences($container, $originalPath, $newPath);
            });
    }
}
