<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use Statamic\Support\DateFormat;

class DateFormatTest extends TestCase
{
    /** @test */
    public function it_has_conversions()
    {
        $this->assertCount(38, DateFormat::phpToMomentConversions());
    }

    /**
     * @test
     * @dataProvider formatProvider
     **/
    public function it_converts_from_php_to_moment($php, $moment)
    {
        $this->assertEquals($moment, DateFormat::toMoment($php));
    }

    public function formatProvider()
    {
        return collect(DateFormat::phpToMomentConversions())->mapWithKeys(function ($moment, $php) {
            return [$php => [$php, $moment]];
        })->all();
    }
}
