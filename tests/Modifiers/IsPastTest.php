<?php

namespace Tests\Modifiers;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsPastTest extends TestCase
{
    public static function dates(): array
    {
        $futureDate = Carbon::createFromDate(2030, 10, 21);
        $pastDate = Carbon::createFromDate(2015, 10, 21);

        return [
            // October 21 2030
            'future_date' => [false, $futureDate->format('F d Y')],
            'future_date_iso_format' => [false, $futureDate->toDateString()],
            'future_date_formatted_string' => [false, $futureDate->toFormattedDateString()],
            'today' => [true, Carbon::now()->format('F d Y')],
            // October 21 2015
            'past_date' => [true, $pastDate->format('F d Y')],
        ];
    }

    #[Test]
    #[DataProvider('dates')]
    public function it_returns_true_if_date_is_past($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isPast()->fetch();
    }
}
