<?php

namespace Statamic\Testing;

use Statamic\Extend\Manifest;
use Tests\Fixtures\TestManifest;

trait ModifiesAddonManifest
{
    protected $manifest;

    protected function fakeManifest()
    {
        $this->manifest = new TestManifest;

        $this->app->instance(Manifest::class, $this->manifest);
    }

    protected function overrideManifest($attrs)
    {
        $firstItem = $this->manifest->manifest['Bar'];

        $firstItem = array_merge($firstItem, $attrs);

        $this->manifest->manifest['Bar'] = $firstItem;
    }
}
