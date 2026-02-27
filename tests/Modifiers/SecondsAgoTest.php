<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SecondsAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_seconds_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20 13:10:30'));

        $this->assertSame($expected, $this->modify(Carbon::parse($input)));
    }

    public static function dateProvider()
    {
        return [
            // Carbon 3 would return floats but to preserve backwards compatibility
            // with Carbon 2 we will cast to integers.
            'same second' => ['2025-02-20 13:10:30', 0], // 0.0
            '1 second ago' => ['2025-02-20 13:10:29', 1], // 1.0
            '2 seconds ago' => ['2025-02-20 13:10:28', 2], // 2.0

            // Future dates would return negative numbers in Carbon 3 but to preserve
            // backwards compatibility with Carbon 2, we keep them positive.
            'one second from now' => ['2025-02-20 13:10:31', 1], // -1.0
            'two seconds from now' => ['2025-02-20 13:10:32', 2], // -2.0
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->secondsAgo()->fetch();
    }
}
