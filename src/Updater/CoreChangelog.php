<?php

namespace Statamic\Updater;

use Statamic\Statamic;

class CoreChangelog extends Changelog
{
    public function slug()
    {
        return Statamic::CORE_SLUG;
    }

    public function currentVersion()
    {
        return Statamic::version();
    }
}
