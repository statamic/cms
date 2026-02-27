<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class DaysAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_days_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20 00:00'));

        $this->assertSame($expected, $this->modify(Carbon::parse($input)));
    }

    public static function dateProvider()
    {
        return [
            // Carbon 3 would return floats but to preserve backwards compatibility
            // with Carbon 2 we will cast to integers.
            'same time' => ['2025-02-20 00:00', 0],
            'less than a day ago' => ['2025-02-19 11:00', 0],
            '1 day ago' => ['2025-02-19 00:00', 1],
            '2 days ago' => ['2025-02-18 00:00', 2],

            // Future dates would return negative numbers in Carbon 3 but to preserve
            // backwards compatibility with Carbon 2, we keep them positive.
            'one day from now' => ['2025-02-21 00:00', 1],
            'less than a day from now' => ['2025-02-20 13:00', 0],
            'more than a day from now' => ['2025-02-21 13:00', 1],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->daysAgo()->fetch();
    }
}
