<?php

namespace Statamic\Console\Composer;

use Composer\Script\Event;

class Scripts
{
    /**
     * Run Statamic pre-update-cmd hook.
     *
     * @param  Event  $event
     */
    public static function preUpdateCmd(Event $event)
    {
        Lock::backup();
    }
}
