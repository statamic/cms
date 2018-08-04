<?php

namespace Statamic\Stache;

use Illuminate\Support\Facades\Cache;
use Facades\Statamic\Stache\Traverser;

class StacheUpdater
{
    protected $stache;

    public function __construct($stache)
    {
        $this->stache = $stache;
    }

    public function update()
    {
        foreach ($this->stache->stores() as $store) {
            $files = Traverser::traverse($store);

            app(StoreUpdater::class)->store($store)->update();
        }
    }
}
