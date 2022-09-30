<?php

namespace Statamic;

use Facades\Statamic\Console\Processes\Composer;

class Version
{
    public function get()
    {
        $currentVersion = Composer::installedVersion(Statamic::PACKAGE);

        if ($currentVersion === null) {
            throw new \Exception('Statamic version could not be found. Does the composer.lock file exist?');
        }

        return $currentVersion;
    }
}
