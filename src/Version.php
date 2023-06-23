<?php

namespace Statamic;

use Facades\Statamic\Console\Processes\Composer;

class Version
{
    public function get()
    {
        $currentVersion = Composer::installedVersion(Statamic::PACKAGE);

        if (! $currentVersion) {
            throw new \Exception('Statamic version could not be found. The composer.lock file is missing.');
        }

        return $currentVersion;
    }
}
