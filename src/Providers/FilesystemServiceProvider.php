<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Assets\AssetContainerContents;
use Statamic\Filesystem\FilesystemAdapter;

class FilesystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FilesystemAdapter::class, function () {
            return new FilesystemAdapter($this->app->make('files'), base_path());
        });

        $this->app->bind(AssetContainerContents::class, function () {
            return new AssetContainerContents();
        });

        $paths = [
            'standard' => base_path(),
            'content' => base_path('content'),
            'resources' => base_path('resources'),
            'users' => base_path('users'),
            'storage' => storage_path('statamic'),
        ];

        // todo:
        if (app()->environment() === 'testing') {
            $paths['content'] = statamic_path('tests/fixtures/content');
            $paths['users'] = statamic_path('tests/fixtures/users');
        }

        foreach ($paths as $key => $path) {
            $this->app->bind("filesystems.paths.$key", function () use ($path) {
                return $path;
            });
        }
    }
}
