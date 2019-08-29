<?php

namespace Tests;

use Statamic\API\Stache;

trait PreventSavingStacheItemsToDisk
{
    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected function preventSavingStacheItemsToDisk()
    {
        Stache::stores()->each(function ($store) {
            $dir = __DIR__.'/__fixtures__';
            $relative = str_after(str_after($store->directory(), $dir), '/');
            $store->directory($this->fakeStacheDirectory . '/' . $relative);
        });
    }

    protected function deleteFakeStacheDirectory()
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);

        mkdir($this->fakeStacheDirectory);
        touch($this->fakeStacheDirectory.'/.gitkeep');
    }
}
