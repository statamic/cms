<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Facades\Glide;

class ClearAssetGlideCache implements ShouldQueue
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(AssetDeleted::class, self::class.'@handleDeleted');
        $events->listen(AssetSaved::class, self::class.'@handleSaved');
    }

    /**
     * Handle the AssetDeleted event.
     *
     * @param  AssetDeleted  $event
     */
    public function handleDeleted($event)
    {
        Glide::clearAsset($event->asset);
    }

    /**
     * Handle the AssetSaved event.
     *
     * @param  AssetSaved  $event
     */
    public function handleSaved($event)
    {
        if ($event->asset->getOriginal('focus') != $event->asset->get('focus')) {
            Glide::clearAsset($event->asset);
        }
    }
}
