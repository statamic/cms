<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use Statamic\Support\DateFormat;

class DateFormatTest extends TestCase
{
    public static function formatProvider()
    {
        return collect(DateFormat::phpToIsoConversions())->mapWithKeys(function ($iso, $php) {
            return [$php => [$php, $iso]];
        })->all();
    }
}
