<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class HoursAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_minutes_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20 13:10:00'));

        $this->assertSame($expected, round($this->modify(Carbon::parse($input)), 2));
    }

    public static function dateProvider()
    {
        return [
            'same time' => ['2025-02-20 13:10:00', 0.0],
            'less than a hour ago' => ['2025-02-20 13:00:00', 0.17],
            '1 hour ago' => ['2025-02-20 12:10:00', 1.0],
            '2 hours ago' => ['2025-02-20 11:10:00', 2.0],
            'one hour from now' => ['2025-02-20 14:10:00', -1.0],
            'less than a hour from now' => ['2025-02-20 13:30:00', -0.33],
            'more than a hour from now' => ['2025-02-20 15:10:00', -2.0],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->hoursAgo()->fetch();
    }
}
