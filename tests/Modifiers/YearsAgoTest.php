<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class YearsAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_years_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20 00:00'));

        $this->assertSame($expected, $this->modify(Carbon::parse($input)));
    }

    public static function dateProvider()
    {
        return [
            // Carbon 3 would return floats but to preserve backwards compatibility
            // with Carbon 2 we will cast to integers.
            '2 years' => ['2023-02-20', 2], // 2.0
            'not quite 3 years' => ['2022-08-20', 2], // 2.5
            '3 years' => ['2022-02-20', 3], // 3.0

            // Future dates would return negative numbers in Carbon 3 but to preserve
            // backwards compatibility with Carbon 2, we keep them positive.
            '1 year from now' => ['2026-02-20', 1], // -1.0
            'less than a year from now' => ['2025-12-20', 0], // -0.83
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->yearsAgo()->fetch();
    }
}
