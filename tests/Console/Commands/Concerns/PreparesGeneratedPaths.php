<?php

namespace Tests\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

trait PreparesGeneratedPaths
{
    protected $testedPaths = [];

    protected function preparePath($path)
    {
        $path = base_path($path);

        $this->testedPaths[] = $path;

        return $path;
    }

    protected function cleanupPaths()
    {
        $files = app(Filesystem::class);

        foreach ($this->testedPaths as $path) {
            $files->isDirectory($path)
                ? $files->deleteDirectory($path)
                : $files->delete($path);
        }

        $dirs = [
            base_path('addons'),
            base_path('app/Actions'),
            base_path('app/Fieldtypes'),
            base_path('app/Modifiers'),
            base_path('app/Scopes'),
            base_path('app/Tags'),
            base_path('app/Widgets'),
            resource_path('js/components'),
        ];

        foreach ($dirs as $dir) {
            $files->deleteDirectory($dir, true);
        }
    }
}
