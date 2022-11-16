<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Illuminate\Support\Carbon;

/** @group graphql */
class DateFieldtypeTest extends FieldtypeTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Carbon::macro('getToStringFormat', function () {
            return static::$toStringFormat;
        });
    }

    /** @test */
    public function it_gets_dates()
    {
        // Set the to string format so can see it uses that rather than a coincidence.
        // But reset it afterwards.
        $originalFormat = Carbon::getToStringFormat();
        Carbon::setToStringFormat('Y-m-d g:ia');

        $this->createEntryWithFields([
            'filled' => [
                'value' => '2017-12-25 13:29',
                'field' => ['type' => 'date', 'time_enabled' => true],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'date', 'time_enabled' => true],
            ],
        ]);

        $query = <<<'GQL'
default: filled
formatted: filled(format: "U")
undefined
GQL;

        $this->assertGqlEntryHas($query, [
            'default' => '2017-12-25 1:29pm',
            'formatted' => '1514208540',
            'undefined' => null,
        ]);

        Carbon::setToStringFormat($originalFormat);
    }

    /** @test */
    public function it_gets_date_ranges()
    {
        // Set the to string format so can see it uses that rather than a coincidence.
        // But reset it afterwards.
        $originalFormat = Carbon::getToStringFormat();
        Carbon::setToStringFormat('Y-m-d g:ia');

        $this->createEntryWithFields([
            'filled' => [
                'value' => [
                    'start' => '2017-12-25',
                    'end' => '2020-04-27',
                ],
                'field' => ['type' => 'date', 'mode' => 'range'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'date', 'mode' => 'range'],
            ],
        ]);

        $query = <<<'GQL'
default: filled { start, end }
formatted: filled { start(format: "U"), end(format: "U") }
undefined { start, end }
GQL;

        $this->assertGqlEntryHas($query, [
            'default' => ['start' => '2017-12-25 12:00am', 'end' => '2020-04-27 12:00am'],
            'formatted' => ['start' => '1514160000', 'end' => '1587945600'],
            'undefined' => null,
        ]);

        Carbon::setToStringFormat($originalFormat);
    }
}
