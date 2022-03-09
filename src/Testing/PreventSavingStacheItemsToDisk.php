<?php

namespace Statamic\Testing;

use function app;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use function str_after;

trait PreventSavingStacheItemsToDisk
{
    protected $fakeStacheDirectory = __DIR__.'/../../tests/__fixtures__/dev-null';

    protected function preventSavingStacheItemsToDisk()
    {
        $this->fakeStacheDirectory = Path::tidy($this->fakeStacheDirectory);

        Stache::stores()->each(function ($store) {
            $dir = Path::tidy(Fixture::path('/'));
            $relative = str_after($store->directory(), $dir);
            $store->directory($this->fakeStacheDirectory.'/'.$relative);
        });
    }

    protected function deleteFakeStacheDirectory()
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);

        mkdir($this->fakeStacheDirectory);
        touch($this->fakeStacheDirectory.'/.gitkeep');
    }
}
