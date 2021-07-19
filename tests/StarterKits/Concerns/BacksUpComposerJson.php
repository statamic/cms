<?php

namespace Tests\StarterKits\Concerns;

use Illuminate\Filesystem\Filesystem;

trait BacksUpComposerJson
{
    protected function backupComposerJson()
    {
        $files = app(Filesystem::class);

        if (! $files->exists(base_path('composer.json.bak'))) {
            $files->copy(base_path('composer.json'), base_path('composer.json.bak'));
        }
    }

    protected function restoreComposerJson()
    {
        $files = app(Filesystem::class);

        if ($files->exists(base_path('composer.json.bak'))) {
            $files->copy(base_path('composer.json.bak'), base_path('composer.json'));
            $files->delete(base_path('composer.json.bak'));
        }
    }
}
