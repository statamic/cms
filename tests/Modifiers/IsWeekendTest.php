<?php

namespace Tests\Modifiers;

use Carbon\Carbon;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsWeekendTest extends TestCase
{
    public static function datesProvider(): array
    {
        $sunday = Carbon::createFromDate(2022, 2, 13);
        $monday = Carbon::createFromDate(2022, 2, 14);

        return [
            'tomorrow' => [false, $monday->format('F d Y')],
            'today' => [true, $sunday->format('F d Y')],
        ];
    }

    /**
     * @test
     *
     * @dataProvider datesProvider
     */
    public function it_returns_true_if_date_is_weekend($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isWeekend()->fetch();
    }
}
