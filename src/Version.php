<?php

namespace Statamic;

use Facades\Statamic\Console\Processes\Composer;

class Version
{
    public function get()
    {
        return Composer::installedVersion(Statamic::PACKAGE);
    }
}
