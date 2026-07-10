<?php

namespace Tests\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

trait CleansUpGeneratedPaths
{
    protected function cleanupPaths()
    {
        $dirs = [
            base_path('addons'),
            base_path('app/Actions'),
            base_path('app/Dictionaries'),
            base_path('app/Fieldtypes'),
            base_path('app/Modifiers'),
            base_path('app/Scopes'),
            base_path('app/Tags'),
            base_path('app/Widgets'),
            resource_path('js/components'),
            resource_path('views/widgets'),
            base_path('vendor'),
            public_path('vendor'),
            __DIR__.'/../../../../resources/dist-dev',
        ];

        foreach ($dirs as $dir) {
            app(Filesystem::class)->deleteDirectory($dir, true);
        }

        $files = [
            base_path('app/Providers/AppServiceProvider.php'),
            resource_path('css/cp.css'),
            resource_path('js/cp.js'),
            base_path('package.json'),
            base_path('vite-cp.config.js'),
        ];

        foreach ($files as $file) {
            app(Filesystem::class)->delete($file);
        }
    }
}
