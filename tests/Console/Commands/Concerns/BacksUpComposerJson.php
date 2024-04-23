<?php

namespace Tests\Console\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

trait BacksUpComposerJson
{
    protected function backupComposerJson()
    {
        app(Filesystem::class)->copy(base_path('composer.json'), base_path('composer.json.bak'));
    }

    protected function restoreComposerJson()
    {
        $files = app(Filesystem::class);
        $files->copy(base_path('composer.json.bak'), base_path('composer.json'));
        $files->delete(base_path('composer.json.bak'));
    }
}
