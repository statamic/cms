<?php declare(strict_types=1);

namespace Tests\Modifiers;

use Carbon\Carbon;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsFutureTest extends TestCase
{
    public function dates(): array
    {
        $futureDate = Carbon::createFromDate(2030, 10, 21);
        $pastDate = Carbon::createFromDate(2015, 10, 21);

        return [
            // October 21 2030
            'future_date' => [true, $futureDate->format('F d Y')],
            'future_date_iso_format' => [true, $futureDate->toDateString()],
            'future_date_formatted_string' => [true, $futureDate->toFormattedDateString()],
            'today' => [false, Carbon::now()->format('F d Y')],
            // October 21 2015
            'past_date' => [false, $pastDate->format('F d Y')],
        ];
    }

    /**
     * @test
     * @dataProvider dates
     */
    public function it_returns_true_if_date_is_future($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isFuture()->fetch();
    }
}
