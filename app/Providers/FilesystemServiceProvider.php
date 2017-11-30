<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Filesystem\FilesystemAdapter;

class FilesystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FilesystemAdapter::class, function () {
            return new FilesystemAdapter($this->app->make('files'), base_path());
        });
    }
}
