<?php

namespace Tests\Git\Concerns;

use Illuminate\Filesystem\Filesystem;
use Statamic\Console\Processes\Process;
use Statamic\Facades\Path;

trait PreparesTempRepos
{
    protected function createTempDirectory($path)
    {
        $files = app(Filesystem::class);

        $path = Path::resolve($path);

        if (! $files->exists($path)) {
            $files->makeDirectory($path, 0755, true);
        } else {
            $files->cleanDirectory($path);
        }
    }

    protected function deleteTempDirectory($path)
    {
        $files = app(Filesystem::class);

        $path = Path::resolve($path);

        if ($files->exists($path)) {
            $files->deleteDirectory($path);
        }
    }

    protected function createTempRepo($path)
    {
        $path = Path::resolve($path);

        $process = Process::create($path);

        $process->run('git init -b master');
        $process->run('git add --all');
        $process->run('git -c "user.name=Tests" -c "user.email=tests@example.com" commit -m "Initial commit."');

        if (static::isRunningWindows()) {
            $process->run("chmod -R 0755 {$path}");
        }
    }
}
