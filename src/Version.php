<?php

namespace Statamic;

use Facades\Statamic\Console\Processes\Composer;

class Version
{
    private static ?string $version = null;

    public function get()
    {
        if (static::$version !== null) {
            return static::$version;
        }

        $currentVersion = Composer::installedVersion(Statamic::PACKAGE);

        if (! $currentVersion) {
            throw new \Exception('Statamic version could not be found. The composer.lock file is missing.');
        }

        return static::$version = $currentVersion;
    }
}
