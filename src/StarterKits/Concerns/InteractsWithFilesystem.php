<?php

namespace Statamic\StarterKits\Concerns;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Path;

trait InteractsWithFilesystem
{
    /**
     * Install starter kit file.
     *
     * @param  mixed  $fromPath
     * @param  mixed  $toPath
     * @return $this
     */
    protected function installFile($fromPath, $toPath, $console)
    {
        $displayPath = str_replace(Path::tidy(base_path().'/'), '', $toPath);

        $console->line("Installing file [{$displayPath}]");

        app(Filesystem::class)->copy($fromPath, $this->preparePath($toPath));

        return $this;
    }

    /**
     * Prepare path directory.
     *
     * @param  string  $path
     * @return string
     */
    protected function preparePath($path)
    {
        $files = app(Filesystem::class);

        $directory = $files->isDirectory($path)
            ? $path
            : preg_replace('/(.*)\/[^\/]*/', '$1', Path::tidy($path));

        if (! $files->exists($directory)) {
            $files->makeDirectory($directory, 0755, true);
        }

        return Path::tidy($path);
    }
}
