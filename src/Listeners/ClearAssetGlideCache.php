<?php

namespace Statamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Statamic\Contracts\Assets\Asset;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\Subscriber;
use Statamic\Facades\Glide;

class ClearAssetGlideCache extends Subscriber implements ShouldQueue
{
    protected $listeners = [
        AssetSaved::class => 'handleSaved',
        AssetDeleted::class => 'handleDeleted',
        AssetReuploaded::class => 'handleReuploaded',
    ];

    public function handleReuploaded(AssetReuploaded $event)
    {
        $this->clear($event->asset);
    }

    public function handleDeleted(AssetDeleted $event)
    {
        $this->clear($event->asset);
    }

    public function handleSaved($event)
    {
        if ($event->asset->getOriginal('data.focus') != $event->asset->get('focus')) {
            $this->clear($event->asset);
        }
    }

    private function clear(Asset $asset)
    {
        Glide::clearAsset($asset);
    }
}
