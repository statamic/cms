<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;
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
        $events->listen(AssetReuploaded::class, self::class.'@handle');
        $events->listen(AssetDeleted::class, self::class.'@handle');
    }

    /**
     * Handle the events.
     *
     * @param  AssetDeleted  $event
     */
    public function handle($event)
    {
        Glide::clearAsset($event->asset);
    }
}
