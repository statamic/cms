<?php

namespace Statamic\StarterKits\Concerns;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Statamic\Console\NullConsole;
use Statamic\Facades\Path;

trait InteractsWithFilesystem
{
    /**
     * Install starter kit file.
     */
    protected function installFile(string $fromPath, string $toPath, Command|NullConsole $console): self
    {
        $displayPath = str_replace(Path::tidy(base_path().'/'), '', $toPath);

        $console->line("Installing file [{$displayPath}]");

        app(Filesystem::class)->copy($fromPath, $this->preparePath($toPath));

        return $this;
    }

    /**
     * Export relative path to starter kit.
     */
    protected function exportRelativePath(string $starterKitPath, string $from, ?string $to = null): void
    {
        $to = $to
            ? "{$starterKitPath}/{$to}"
            : "{$starterKitPath}/{$from}";

        $from = base_path($from);

        $this->preparePath($to);

        $files = app(Filesystem::class);

        $files->isDirectory($from)
            ? $files->copyDirectory($from, $to)
            : $files->copy($from, $to);
    }

    /**
     * Copy directory contents into, file by file so that it does not stomp the whole target directory.
     */
    protected function copyDirectoryContentsInto(string $from, string $to): void
    {
        $files = app(Filesystem::class);

        collect($files->allFiles($from))
            ->mapWithKeys(fn ($file) => [$from.'/'.$file->getRelativePathname() => $to.'/'.$file->getRelativePathname()])
            ->each(fn ($to, $from) => $files->copy(Path::tidy($from), $this->preparePath($to)));
    }

    /**
     * Prepare path directory.
     */
    protected function preparePath(string $path): string
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
