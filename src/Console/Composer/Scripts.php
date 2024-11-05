<?php

namespace Statamic\Console\Composer;

use Composer\Script\Event;

class Scripts
{
    /**
     * Run Statamic pre-update-cmd hook.
     */
    public static function preUpdateCmd(Event $event)
    {
        Lock::backup();
    }

    public static function prePackageUninstall($event)
    {
        passthru(sprintf(
            'php artisan statamic:addons:uninstall %s --no-interaction',
            $event->getOperation()->getPackage()->getName()
        ));
    }
}
