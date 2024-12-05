<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;

trait UnlinksPaths
{
    protected $unlinkablePaths = [];

    /**
     * Unlink passed path/paths after test (during tearDown).
     *
     * @param  string|array|\Illuminate\Support\Collection  $paths
     */
    protected function unlinkAfter($paths)
    {
        if (func_num_args() > 1) {
            $paths = func_get_args();
        }

        collect($paths)->each(function ($path) {
            $this->unlinkablePaths[] = $path;
        });
    }

    /**
     * Unlink set paths.
     */
    public function unlinkPaths()
    {
        collect($this->unlinkablePaths)->each(function ($path) {
            @unlink($path);
        });
    }

    /**
     * Unlink paths on tearDown.
     *
     * Note: The trait's tearDown() will not run if test case defines it's own tearDown().
     * In which case, you can manually call unlinkPaths() as necessary.
     */
    public function tearDown(): void
    {
        $this->unlinkPaths();

        parent::tearDown();
    }
}
