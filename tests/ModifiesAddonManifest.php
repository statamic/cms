<?php

namespace Tests;

use Statamic\Addons\Manifest;

trait ModifiesAddonManifest
{
    protected $manifest;

    protected function fakeManifest()
    {
        $this->manifest = new \Tests\Fixtures\TestManifest;

        $this->app->instance(Manifest::class, $this->manifest);
    }

    protected function overrideManifest($attrs)
    {
        $firstItem = $this->manifest->manifest['Bar'];

        $firstItem = array_merge($firstItem, $attrs);

        $this->manifest->manifest['Bar'] = $firstItem;
    }
}
