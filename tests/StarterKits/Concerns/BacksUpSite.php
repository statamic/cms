<?php

namespace Tests\StarterKits\Concerns;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\Path;

trait BacksUpSite
{
    protected function backupSite()
    {
        $files = app(Filesystem::class);

        $backupPath = Path::resolve(base_path('../site-backup'));

        if (! $files->exists($backupPath)) {
            $files->copyDirectory(base_path(), $backupPath);
        }
    }

    protected function restoreSite()
    {
        $files = app(Filesystem::class);

        $backupPath = Path::resolve(base_path('../site-backup'));
        $basePath = base_path();

        if ($files->exists($backupPath)) {
            $files->cleanDirectory($basePath);
            $files->copyDirectory($backupPath, $basePath);
            $files->deleteDirectory($backupPath);
        }
    }
}
