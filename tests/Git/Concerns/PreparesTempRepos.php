<?php

namespace Tests\Git\Concerns;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Processes\Process;

trait PreparesTempRepos
{
    protected function createTempDirectory($path)
    {
        $files = app(Filesystem::class);

        if (! $files->exists($path)) {
            $files->makeDirectory($path, 0755, true);
        } else {
            $files->cleanDirectory($path);
        }
    }

    protected function deleteTempDirectory($path)
    {
        $files = app(Filesystem::class);

        if ($files->exists($path)) {
            $files->deleteDirectory($path);
        }
    }

    protected function createTempRepo($path)
    {
        $process = Process::create($path);

        $process->run('git init');
        $process->run('git add --all');
        $process->run('git commit -m "Initial commit."');
    }
}
