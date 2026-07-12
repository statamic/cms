<?php

namespace Tests\Modifiers;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsWeekdayTest extends TestCase
{
    public static function datesProvider(): array
    {
        $sunday = Carbon::createFromDate(2022, 2, 13);
        $monday = Carbon::createFromDate(2022, 2, 14);

        return [
            'tomorrow' => [true, $monday->format('F d Y')],
            'today' => [false, $sunday->format('F d Y')],
        ];
    }

    #[Test]
    #[DataProvider('datesProvider')]
    public function it_returns_true_if_date_is_weekday($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isWeekday()->fetch();
    }
}
