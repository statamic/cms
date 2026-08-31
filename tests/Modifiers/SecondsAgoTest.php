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

        $this->assertSame($expected, round($this->modify(Carbon::parse($input)), 2));
    }

    public static function dateProvider()
    {
        return [
            'same second' => ['2025-02-20 13:10:30', 0.0],
            '1 second ago' => ['2025-02-20 13:10:29', 1.0],
            '2 seconds ago' => ['2025-02-20 13:10:28', 2.0],
            'one second from now' => ['2025-02-20 13:10:31', -1.0],
            'two seconds from now' => ['2025-02-20 13:10:32', -2.0],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->secondsAgo()->fetch();
    }
}
