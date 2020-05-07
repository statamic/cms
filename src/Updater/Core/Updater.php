<?php

namespace Statamic\Updater\Core;

use Statamic\Statamic;
use Statamic\Updater\Updater as BaseUpdater;

class Updater extends BaseUpdater
{
    /**
     * Get package.
     *
     * @return string
     */
    protected function getPackage()
    {
        return Statamic::PACKAGE;
    }
}
