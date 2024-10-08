<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Statamic\Support\DateFormat;

class DateFormatTest extends TestCase
{
    #[Test]
    public function it_has_conversions()
    {
        $this->assertCount(38, DateFormat::phpToIsoConversions());
    }

    #[Test]
    #[DataProvider('formatProvider')]
    public function it_converts_from_php_to_iso($php, $iso)
    {
        $this->assertEquals($iso, DateFormat::toIso($php));
    }

    public static function formatProvider()
    {
        return collect(DateFormat::phpToIsoConversions())->mapWithKeys(function ($iso, $php) {
            return [$php => [$php, $iso]];
        })->all();
    }
}
