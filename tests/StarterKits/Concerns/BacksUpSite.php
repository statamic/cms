<?php

namespace Tests\StarterKits\Concerns;

use Illuminate\Filesystem\Filesystem;

trait BacksUpSite
{
    protected function backupSite()
    {
        $files = app(Filesystem::class);

        if (! $files->exists(base_path('../site-backup'))) {
            $files->copyDirectory(base_path(), base_path('../site-backup'));
        }
    }

    protected function restoreSite()
    {
        $files = app(Filesystem::class);

        $basePath = base_path();

        if ($files->exists(base_path('../site-backup'))) {
            $files->cleanDirectory($basePath);
            $files->copyDirectory($basePath.'/../site-backup', $basePath, true);
            $files->deleteDirectory($basePath.'/../site-backup');
        }
    }
}
