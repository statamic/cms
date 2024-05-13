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
            base_path('app/Fieldtypes'),
            base_path('app/Modifiers'),
            base_path('app/Scopes'),
            base_path('app/Tags'),
            base_path('app/Widgets'),
            resource_path('js/components'),
            base_path('vendor'),
        ];

        foreach ($dirs as $dir) {
            app(Filesystem::class)->deleteDirectory($dir, true);
        }
    }
}
