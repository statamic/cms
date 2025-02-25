<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class MinutesAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_minutes_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20 13:10:00'));

        $this->assertSame($expected, $this->modify(Carbon::parse($input)));
    }

    public static function dateProvider()
    {
        return [
            'same time' => ['2025-02-20 13:10:00', 0.0],
            'less than a minute ago' => ['2025-02-20 13:09:30', 0.5],
            '1 minute ago' => ['2025-02-20 13:09:00', 1.0],
            '2 minutes ago' => ['2025-02-20 13:08:00', 2.0],
            'one minute from now' => ['2025-02-20 13:11:00', -1.0],
            'less than a minute from now' => ['2025-02-20 13:10:30', -0.5],
            'more than a minute from now' => ['2025-02-20 13:11:30', -1.5],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->minutesAgo()->fetch();
    }
}
