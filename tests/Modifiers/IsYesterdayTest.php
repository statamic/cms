<?php

namespace Tests\Modifiers;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsYesterdayTest extends TestCase
{
    public static function datesProvider(): array
    {
        $futureDate = Carbon::createFromDate(2030, 10, 21);
        $tomorrow = Carbon::tomorrow();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $pastDate = Carbon::createFromDate(2015, 10, 21);

        return [
            'tomorrow' => [false, $tomorrow->format('F d Y')],
            'today' => [false, $today->format('F d Y')],
            'future_date' => [false, $futureDate->format('F d Y')],
            'yesterday' => [true, $yesterday->format('F d Y')],
            'past_date' => [false, $pastDate->format('F d Y')],
        ];
    }

    #[Test]
    #[DataProvider('datesProvider')]
    public function it_returns_true_if_date_is_yesterday($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isYesterday()->fetch();
    }
}
