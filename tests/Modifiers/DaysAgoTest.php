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

        $this->assertSame($expected, round($this->modify(Carbon::parse($input))));
    }

    public static function dateProvider()
    {
        return [
            'same time' => ['2025-02-20 00:00', 0.0],
            'less than a day ago' => ['2025-02-19 11:00', 1.0],
            '1 day ago' => ['2025-02-19 00:00', 1.0],
            '2 days ago' => ['2025-02-18 00:00', 2.0],

            'one day from now' => ['2025-02-21 00:00', -1.0],
            'less than a day from now' => ['2025-02-20 13:00', -1.0],
            'more than a day from now' => ['2025-02-21 13:00', -2.0],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->daysAgo()->fetch();
    }
}
