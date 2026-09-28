<?php

namespace Statamic\Listeners;

use Statamic\Events\AssetContainerCreated;
use Statamic\Events\CollectionCreated;
use Statamic\Events\GlobalSetCreated;
use Statamic\Events\NavCreated;
use Statamic\Events\Subscriber;
use Statamic\Events\TaxonomyCreated;
use Statamic\Facades\CP\Nav;

class InvalidateNavCache extends Subscriber
{
    protected $listeners = [
        CollectionCreated::class => 'invalidate',
        NavCreated::class => 'invalidate',
        TaxonomyCreated::class => 'invalidate',
        AssetContainerCreated::class => 'invalidate',
        GlobalSetCreated::class => 'invalidate',
    ];

    public function invalidate($event): void
    {
        Nav::clearCachedUrls();
    }
}
