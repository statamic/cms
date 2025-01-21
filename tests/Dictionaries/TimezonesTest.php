<?php

namespace Tests\Dictionaries;

use DateTimeZone;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Item;
use Statamic\Dictionaries\Timezones;
use Tests\TestCase;

class TimezonesTest extends TestCase
{
    #[Test]
    public function it_gets_options()
    {
        $options = (new Timezones)->options();

        $this->assertCount(count(DateTimeZone::listIdentifiers()), $options);
        $this->assertEquals([
            'Africa/Abidjan' => 'Africa/Abidjan (+00:00)',
            'Africa/Accra' => 'Africa/Accra (+00:00)',
            'Africa/Addis_Ababa' => 'Africa/Addis_Ababa (+03:00)',
            'Pacific/Wake' => 'Pacific/Wake (+12:00)',
            'Pacific/Wallis' => 'Pacific/Wallis (+12:00)',
            'UTC' => 'UTC (+00:00)',
        ], [...array_slice($options, 0, 3), ...array_slice($options, -3, 3)]);
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_searches_options($query, $expected)
    {
        // UTC offsets can change during daylight saving time, so we need to freeze time.
        Carbon::setTestNow('2024-07-23');

        $this->assertEquals($expected, (new Timezones)->options($query));
    }

    public static function searchProvider()
    {
        return [
            'new' => [
                'new',
                [
                    'America/New_York' => 'America/New_York (-04:00)',
                    'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem (-05:00)',
                ],
            ],
            'ten' => [
                '10',
                [
                    'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville (+10:00)',
                    'Antarctica/Macquarie' => 'Antarctica/Macquarie (+10:00)',
                    'Asia/Ust-Nera' => 'Asia/Ust-Nera (+10:00)',
                    'Asia/Vladivostok' => 'Asia/Vladivostok (+10:00)',
                    'Australia/Brisbane' => 'Australia/Brisbane (+10:00)',
                    'Australia/Hobart' => 'Australia/Hobart (+10:00)',
                    'Australia/Lindeman' => 'Australia/Lindeman (+10:00)',
                    'Australia/Lord_Howe' => 'Australia/Lord_Howe (+10:30)',
                    'Australia/Melbourne' => 'Australia/Melbourne (+10:00)',
                    'Australia/Sydney' => 'Australia/Sydney (+10:00)',
                    'Pacific/Chuuk' => 'Pacific/Chuuk (+10:00)',
                    'Pacific/Guam' => 'Pacific/Guam (+10:00)',
                    'Pacific/Honolulu' => 'Pacific/Honolulu (-10:00)',
                    'Pacific/Port_Moresby' => 'Pacific/Port_Moresby (+10:00)',
                    'Pacific/Rarotonga' => 'Pacific/Rarotonga (-10:00)',
                    'Pacific/Saipan' => 'Pacific/Saipan (+10:00)',
                    'Pacific/Tahiti' => 'Pacific/Tahiti (-10:00)',
                ],
            ],
            'plus ten' => [
                '+10',
                [
                    'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville (+10:00)',
                    'Antarctica/Macquarie' => 'Antarctica/Macquarie (+10:00)',
                    'Asia/Ust-Nera' => 'Asia/Ust-Nera (+10:00)',
                    'Asia/Vladivostok' => 'Asia/Vladivostok (+10:00)',
                    'Australia/Brisbane' => 'Australia/Brisbane (+10:00)',
                    'Australia/Hobart' => 'Australia/Hobart (+10:00)',
                    'Australia/Lindeman' => 'Australia/Lindeman (+10:00)',
                    'Australia/Lord_Howe' => 'Australia/Lord_Howe (+10:30)',
                    'Australia/Melbourne' => 'Australia/Melbourne (+10:00)',
                    'Australia/Sydney' => 'Australia/Sydney (+10:00)',
                    'Pacific/Chuuk' => 'Pacific/Chuuk (+10:00)',
                    'Pacific/Guam' => 'Pacific/Guam (+10:00)',
                    'Pacific/Port_Moresby' => 'Pacific/Port_Moresby (+10:00)',
                    'Pacific/Saipan' => 'Pacific/Saipan (+10:00)',
                ],
            ],
            'minus ten' => [
                '-10',
                [
                    'Pacific/Honolulu' => 'Pacific/Honolulu (-10:00)',
                    'Pacific/Rarotonga' => 'Pacific/Rarotonga (-10:00)',
                    'Pacific/Tahiti' => 'Pacific/Tahiti (-10:00)',
                ],
            ],
        ];
    }

    #[Test]
    public function it_gets_array_from_value()
    {
        // UTC offsets can change during daylight saving time, so we need to freeze time.
        Carbon::setTestNow('2024-07-23');

        $item = (new Timezones)->get('America/New_York');
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals([
            'name' => 'America/New_York',
            'offset' => '-04:00',
        ], $item->data());
    }
}
