<?php

namespace Statamic\Updater;

use Statamic\Statamic;
use Statamic\Support\Str;

class CoreChangelog extends Changelog
{
    public function item()
    {
        return Statamic::PACKAGE;
    }

    public function currentVersion()
    {
        return Statamic::version();
    }
}
