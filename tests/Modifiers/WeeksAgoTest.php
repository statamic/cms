<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class WeeksAgoTest extends TestCase
{
    #[Test]
    #[DataProvider('dateProvider')]
    public function it_outputs_weeks_ago($input, $expected)
    {
        Carbon::setTestNow(Carbon::parse('2025-02-20'));

        $this->assertSame($expected, round($this->modify(Carbon::parse($input)), 2));
    }

    public static function dateProvider()
    {
        return [
            'same day' => ['2025-02-20', 0.0],
            'same week' => ['2025-02-19', 0.14],
            'less than a week ago' => ['2025-02-17', 0.43],
            '1 week ago' => ['2025-02-13', 1.0],
            '2 weeks ago' => ['2025-02-06', 2.0],
            'one week from now' => ['2025-02-27', -1.0],
            'less than a week from now' => ['2025-02-22', -0.29],
            'more than a week from now' => ['2025-03-08', -2.29],
        ];
    }

    public function modify($value)
    {
        return Modify::value($value)->weeksAgo()->fetch();
    }
}
