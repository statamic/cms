<?php

namespace Tests;

trait WindowsHelpers
{
    protected static function isRunningWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    protected function markTestSkippedInWindows(string $message = '')
    {
        if (static::isRunningWindows()) {
            $this->markTestSkipped($message);
        }
    }
}
