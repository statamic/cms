<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class MonthsAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_months_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20'));

        $this->assertSame($expected, round($this->modify(Carbon::parse($input)), 2));
    }

    public static function dateProvider()
    {
        return [
            'same month' => ['2025-02-20', 0.0],
            'less than a month ago' => ['2025-02-10', 0.36],
            '1 month ago' => ['2025-01-20', 1.0],
            '2 months ago' => ['2024-12-20', 2.0],
            'one month from now' => ['2025-03-20', -1.0],
            'less than a month from now' => ['2025-02-25', -0.18],
            'more than a month from now' => ['2025-04-20', -2.0],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->monthsAgo()->fetch();
    }
}
