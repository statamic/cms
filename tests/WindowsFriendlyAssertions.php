<?php

namespace Tests;

trait WindowsFriendlyAssertions
{
    public static function assertEquals($expected, $actual, string $message = ''): void
    {
        $args = static::normalizeArgsForWindows(func_get_args());

        parent::assertEquals(...$args);
    }

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

    protected function isRunningWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    protected function markTestSkippedInWindows()
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }
    }
}
