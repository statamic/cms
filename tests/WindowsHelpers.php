<?php

namespace Tests;

trait WindowsHelpers
{
    protected static function normalizeArgsForWindows($args)
    {
        if (static::isRunningWindows()) {
            return collect($args)
                ->map(function ($arg) {
                    return static::normalizeMultilineStringForWindows($arg);
                })
                ->all();
        }

        return $args;
    }

    protected static function normalizeMultilineStringForWindows($value)
    {
        if (static::isRunningWindows() && is_string($value)) {
            return str_replace("\r\n", "\n", $value);
        }

        return $value;
    }

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
