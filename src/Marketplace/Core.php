<?php

namespace Statamic\Marketplace;

use Statamic\Statamic;
use Statamic\Updater\CoreChangelog;

class Core
{
    public function name()
    {
        return 'Statamic';
    }

    public function package()
    {
        return Statamic::PACKAGE;
    }

    public function changelog()
    {
        return new CoreChangelog;
    }
}
