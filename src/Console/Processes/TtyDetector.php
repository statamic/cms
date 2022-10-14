<?php

namespace Statamic\Console\Processes;

use RuntimeException;
use Symfony\Component\Process\Process;

class TtyDetector
{
    /**
     * Try to `setTty()` using symfony/process, since that method ultimately determines whether or not we can TTY.
     *
     * @return bool
     */
    public function isTtySupported()
    {
        try {
            (new Process([]))->setTty(true);
        } catch (RuntimeException $exception) {
            return false;
        }

        return true;
    }
}
