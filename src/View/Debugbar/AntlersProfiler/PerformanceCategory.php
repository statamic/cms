<?php

namespace Statamic\View\Debugbar\AntlersProfiler;

class PerformanceCategory
{
    const ExecutionTimeGreat = 1;
    const ExecutionTimeGood = 2;
    const ExecutionTimeWarning = 3;
    const ExecutionTimeStrongWarning = 4;
    const ExecutionTimeDanger = 5;

    public static function getCategory($executionTimeMilliseconds)
    {
        // The thresholds should take into account
        // the overhead of being in debug mode/etc.
        // If we make these too low, we'll get
        // into a situation where people go
        // crazy trying to remove warnings
        // when things are already fast.

        if ($executionTimeMilliseconds < 0.2) {
            return self::ExecutionTimeGreat;
        } elseif ($executionTimeMilliseconds <= 70) {
            return self::ExecutionTimeGood;
        } elseif ($executionTimeMilliseconds <= 150) {
            return self::ExecutionTimeWarning;
        } elseif ($executionTimeMilliseconds <= 300) {
            return self::ExecutionTimeStrongWarning;
        }

        return self::ExecutionTimeDanger;
    }
}
