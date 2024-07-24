<?php

namespace Tests\Dictionaries;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Timezones;
use Statamic\Support\Arr;
use Tests\TestCase;

class TimezonesTest extends TestCase
{
    #[Test]
    public function it_gets_options()
    {
        $options = (new Timezones)->options();

        $this->assertCount(419, $options);
        $this->assertEquals([
            'Africa/Abidjan' => 'Africa/Abidjan',
            'Africa/Accra' => 'Africa/Accra',
            'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
            'Pacific/Wake' => 'Pacific/Wake',
            'Pacific/Wallis' => 'Pacific/Wallis',
            'UTC' => 'UTC',
        ], [...Arr::take($options, 3), ...Arr::take($options, -3)]);
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_searches_options($query, $expected)
    {
        $this->assertEquals($expected, (new Timezones)->options($query));
    }

    public static function searchProvider()
    {
        return [
            'new' => [
                'new',
                [
                    'America/New_York' => 'America/New_York',
                    'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem',
                ],
            ],
        ];
    }

    #[Test]
    public function it_gets_array_from_value()
    {
        // UTC offsets can change during daylight saving time, so we need to freeze time.
        Carbon::setTestNow('2024-07-23');

        $this->assertEquals([
            'name' => 'America/New_York',
            'offset' => '-04:00',
        ], (new Timezones)->get('America/New_York'));
    }
}
