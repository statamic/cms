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
        foreach ($this->testedPaths as $path) {
            $this->files->isDirectory($path)
                ? $this->files->deleteDirectory($path)
                : $this->files->delete($path);
        }

        $dirs = [
            base_path('addons'),
            base_path('app/Actions'),
            base_path('app/Fieldtypes'),
            base_path('app/Scopes'),
            base_path('app/Tags'),
            base_path('app/Widgets'),
        ];

        foreach ($dirs as $dir) {
            $this->files->deleteDirectory($dir, true);
        }
    }
}
