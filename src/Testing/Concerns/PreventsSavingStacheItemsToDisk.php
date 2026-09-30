<?php

namespace Statamic\Testing\Concerns;

use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Support\Str;

trait PreventsSavingStacheItemsToDisk
{
    protected function preventSavingStacheItemsToDisk(): void
    {
        $this->fakeStacheDirectory = Path::tidy($this->fakeStacheDirectory);

        Stache::stores()->each(function ($store) {
            $fixturesPath = Str::before($this->fakeStacheDirectory, '/dev-null');
            $storeDirectory = '/'.Path::makeRelative($store->directory());

            $relative = Str::after(Str::after($storeDirectory, $fixturesPath), '/');

            $store->directory($this->fakeStacheDirectory.'/'.$relative);
        });
    }

    protected function deleteFakeStacheDirectory(): void
    {
        app('files')->deleteDirectory($this->fakeStacheDirectory);

        // The directory can outlive deleteDirectory(), which ignores a failed rmdir(). On Windows
        // that happens while another process still holds a handle on it, so only recreate it if it's gone.
        if (! is_dir($this->fakeStacheDirectory)) {
            mkdir($this->fakeStacheDirectory);
        }

        touch($this->fakeStacheDirectory.'/.gitkeep');
    }
}
