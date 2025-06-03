<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Date;
use Tests\TestCase;

class DateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Set "now" to an arbitrary time so we can make sure that when date
        // instances are created in the fieldtype, they aren't inheriting
        // default values from the current time.
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i', '2010-12-25 13:43'));
    }

    #[Test]
    #[DataProvider('augmentProvider')]
    public function it_augments($timezone, $config, $value, $expected)
    {
        config()->set('app.timezone', $timezone);

        $augmented = $this->fieldtype($config)->augment($value);

        $this->assertInstanceOf(Carbon::class, $augmented);
        $this->assertEquals($expected, $augmented->format('Y M d H:i:s'));
    }

    public static function augmentProvider()
    {
        return [
            'date' => [
                'UTC',
                [],
                '2012-01-04',
                '2012 Jan 04 00:00:00',
            ],
            'date with custom format' => [
                'UTC',
                ['format' => 'Y--m--d'],
                '2012--01--04',
                '2012 Jan 04 00:00:00',
            ],
            'date in a different timezone' => [
                'America/New_York', // -5000
                [],
                '2012-01-04',
                '2012 Jan 04 05:00:00',
            ],

            // The time and seconds configs are important, otherwise
            // when when parsing dates without times, the time would inherit from "now".
            // We need to rely on the configs to know when or when not to reset the time.

            'date with time' => [
                'UTC',
                ['time_enabled' => true],
                '2012-01-04 15:32',
                '2012 Jan 04 15:32:00',
            ],
            'date with time but seconds disabled' => [
                'UTC',
                ['time_enabled' => true],
                '2012-01-04 15:32:54',
                '2012 Jan 04 15:32:00',
            ],
            'date with time and seconds' => [
                'UTC',
                ['time_enabled' => true, 'time_seconds_enabled' => true],
                '2012-01-04 15:32:54',
                '2012 Jan 04 15:32:54',
            ],
            'date with time in a different timezone' => [
                'America/New_York', // -5000
                ['time_enabled' => true],
                '2012-01-04 15:32',
                '2012 Jan 04 20:32:00',
            ],
        ];
    }

    #[Test]
    public function it_augments_null()
    {
        $augmented = $this->fieldtype()->augment(null);

        $this->assertNull($augmented);
    }

    #[Test]
    public function it_augments_a_carbon_instance()
    {
        // Could happen if you are using the date fieldtype to augment a manually provided value.

        $instance = new Carbon;
        $augmented = $this->fieldtype()->augment($instance);

        $this->assertSame($instance, $augmented);
    }

    #[Test]
    public function it_augments_a_range()
    {
        $augmented = $this->fieldtype(['mode' => 'range'])->augment([
            'start' => '2012-01-04',
            'end' => '2013-02-06',
        ]);

        $this->assertIsArray($augmented);
        $this->assertEquals(['start', 'end'], array_keys($augmented));
        $this->assertInstanceOf(Carbon::class, $augmented['start']);
        $this->assertInstanceOf(Carbon::class, $augmented['end']);
        $this->assertEquals('2012 Jan 04 00:00', $augmented['start']->format('Y M d H:i'));
        $this->assertEquals('2013 Feb 06 00:00', $augmented['end']->format('Y M d H:i'));
    }

    #[Test]
    public function it_augments_a_null_range()
    {
        $augmented = $this->fieldtype(['mode' => 'range'])->augment(null);

        $this->assertNull($augmented);
    }

    #[Test]
    #[DataProvider('processProvider')]
    public function it_processes_on_save($timezone, $config, $value, $expected)
    {
        config()->set('app.timezone', $timezone);

        $this->assertSame($expected, $this->fieldtype($config)->process($value));
    }

    public static function processProvider()
    {
        return [
            'null' => [
                'UTC',
                [],
                null,
                null,
            ],
            'date with default format' => [
                'UTC',
                [],
                '2012-08-29T00:00:00Z',
                '2012-08-29 00:00',
            ],
            'date with custom format' => [
                'UTC',
                ['format' => 'Y--m--d H/i'],
                '2012-08-29T00:00:00Z',
                '2012--08--29 00/00',
            ],
            'date in a different timezone' => [
                'America/New_York', // -4000
                [],
                '2012-08-29T00:00:00Z',
                '2012-08-28 20:00',
            ],
            'date with time' => [
                'UTC',
                ['time_enabled' => true],
                '2012-08-29T13:43:00Z',
                '2012-08-29 13:43',
            ],
            'null range' => [
                'UTC',
                ['mode' => 'range'],
                ['start' => null, 'end' => null],
                null,
            ],
            'range with default format' => [
                'UTC',
                ['mode' => 'range'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T23:59:00Z'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
            ],
            'range with custom format' => [
                'UTC',
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T23:59:00Z'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 23/59'],
            ],
            'range in a different timezone' => [
                'America/New_York', // -4000
                ['mode' => 'range'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T23:59:00Z'],
                ['start' => '2012-08-28 20:00', 'end' => '2013-09-27 19:59'],
            ],
        ];
    }

    #[Test]
    public function it_saves_date_as_integer_if_format_results_in_a_number()
    {
        $this->assertSame(20120829, $this->fieldtype(['format' => 'Ymd'])->process('2012-08-29T00:00:00Z'));
    }

    #[Test]
    public function it_saves_ranges_as_integers_if_format_results_in_a_number()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'YmdHi']);

        $this->assertSame(
            ['start' => 201208290000, 'end' => 201309272359],
            $fieldtype->process(['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T23:59:00Z'])
        );
    }

    #[Test]
    #[DataProvider('preProcessProvider')]
    public function it_preprocesses($timezone, $config, $value, $expected)
    {
        config()->set('app.timezone', $timezone);

        $this->assertSame($expected, $this->fieldtype($config)->preProcess($value));
    }

    public static function preProcessProvider()
    {
        return [
            'null' => [
                'UTC',
                [],
                null,
                null,
            ],
            'now' => [
                'UTC',
                [],
                'now', // this would happen if the value was null, but default was "now"
                '2010-12-25T13:43:00Z', // current date
            ],
            'date without time' => [
                'UTC',
                [],
                '2012-08-29',
                '2012-08-29T00:00:00Z',
            ],
            'date with default format' => [
                'UTC',
                [],
                '2012-08-29 00:00',
                '2012-08-29T00:00:00Z',
            ],
            'date with custom format' => [
                'UTC',
                ['format' => 'Y--m--d H/i'],
                '2012--08--29 00/00',
                '2012-08-29T00:00:00Z',
            ],
            'date in a different timezone' => [
                'America/New_York', // -0400
                [],
                '2012-08-29 00:00',
                '2012-08-29T04:00:00Z',
            ],
            'date with time' => [
                'UTC',
                ['time_enabled' => true],
                '2012-08-29 13:43',
                '2012-08-29T13:43:00Z',
            ],
            'date with time in a different timezone' => [
                'America/New_York', // -0400
                ['time_enabled' => true],
                '2012-08-29 13:43',
                '2012-08-29T17:43:00Z',
            ],
            'null range' => [
                'UTC',
                ['mode' => 'range'],
                null,
                null,
            ],
            'range with default format' => [
                'UTC',
                ['mode' => 'range'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T23:59:00Z'],
            ],
            'range with custom format' => [
                'UTC',
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 23/59'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T23:59:00Z'],
            ],
            'range in a different timezone' => [
                'America/New_York', // -4000
                ['mode' => 'range'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
                ['start' => '2012-08-29T04:00:00Z', 'end' => '2013-09-28T03:59:00Z'],
            ],
            'range where single date has been provided' => [
                'UTC',
                // e.g. If it was once a non-range field.
                // Use the single date as both the start and end dates.
                ['mode' => 'range'],
                '2012-08-29',
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2012-08-29T23:59:59Z'],
            ],
            'range where single date has been provided with custom format' => [
                'UTC',
                ['mode' => 'range', 'format' => 'Y--m--d'],
                '2012--08--29',
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2012-08-29T23:59:59Z'],
            ],
            'date where range has been provided' => [
                'UTC',
                // e.g. If it was once a range field. Use the start date.
                [],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
                '2012-08-29T00:00:00Z',
            ],
            'date where range has been provided with custom format' => [
                'UTC',
                ['format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 23/59'],
                '2012-08-29T00:00:00Z',
            ],
        ];
    }

    #[Test]
    #[DataProvider('preProcessIndexProvider')]
    public function it_preprocesses_for_index($timezone, $config, $value, $expected)
    {
        config()->set('app.timezone', $timezone);

        $this->assertSame($expected, $this->fieldtype($config)->preProcessIndex($value));
    }

    public static function preProcessIndexProvider()
    {
        return [
            'null' => [
                'UTC',
                [],
                null,
                null,
            ],
            'date with default format' => [
                'UTC',
                [],
                '2012-08-29 00:00',
                ['date' => '2012-08-29T00:00:00Z', 'mode' => 'single', 'time_enabled' => false],
            ],
            'date with custom format' => [
                'UTC',
                ['format' => 'Y--m--d H/i'],
                '2012--08--29 00/00',
                ['date' => '2012-08-29T00:00:00Z', 'mode' => 'single', 'time_enabled' => false],
            ],
            'date in a different timezone' => [
                'America/New_York', // -0400
                [],
                '2012-08-29 00:00',
                ['date' => '2012-08-29T04:00:00Z', 'mode' => 'single', 'time_enabled' => false],
            ],
            'date with time' => [
                'UTC',
                ['time_enabled' => true],
                '2012-08-29 13:43',
                ['date' => '2012-08-29T13:43:00Z', 'mode' => 'single', 'time_enabled' => true],
            ],
            'date with time and custom format' => [
                'UTC',
                ['time_enabled' => true, 'format' => 'Y--m--d H:i'],
                '2012--08--29 13:43',
                ['date' => '2012-08-29T13:43:00Z', 'mode' => 'single', 'time_enabled' => true],
            ],
            'date with time in a different timezone' => [
                'America/New_York', // -0400
                ['time_enabled' => true],
                '2012-08-29 13:43',
                ['date' => '2012-08-29T17:43:00Z', 'mode' => 'single', 'time_enabled' => true],
            ],
            'null range' => [
                'UTC',
                ['mode' => 'range'],
                null,
                null,
            ],
            'range with default format' => [
                'UTC',
                ['mode' => 'range'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T00:00:00Z', 'mode' => 'range', 'time_enabled' => false],
            ],
            'range with custom format' => [
                'UTC',
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 00/00'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T00:00:00Z', 'mode' => 'range', 'time_enabled' => false],
            ],
            'range in a different timezone' => [
                'America/New_York', // -4000
                ['mode' => 'range'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['start' => '2012-08-29T04:00:00Z', 'end' => '2013-09-27T04:00:00Z', 'mode' => 'range', 'time_enabled' => false],
            ],
            'range where single date has been provided' => [
                // e.g. If it was once a non-range field.
                // Use the single date as both the start and end dates.
                'UTC',
                ['mode' => 'range'],
                '2012-08-29',
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2012-08-29T00:00:00Z', 'mode' => 'range', 'time_enabled' => false],
            ],
            'range where single date has been provided with custom format' => [
                'UTC',
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                '2012--08--29 00/00',
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2012-08-29T00:00:00Z', 'mode' => 'range', 'time_enabled' => false],
            ],
            'date where range has been provided' => [
                // e.g. If it was once a range field. Use the start date.
                'UTC',
                [],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['date' => '2012-08-29T00:00:00Z', 'mode' => 'single', 'time_enabled' => false],
            ],
            'date where range has been provided with custom format' => [
                'UTC',
                ['format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 00/00'],
                ['date' => '2012-08-29T00:00:00Z', 'mode' => 'single', 'time_enabled' => false],
            ],
            'range where time has been enabled' => [
                'UTC',
                ['mode' => 'range', 'time_enabled' => true],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['start' => '2012-08-29T00:00:00Z', 'end' => '2013-09-27T00:00:00Z', 'mode' => 'range', 'time_enabled' => true],
            ],
        ];
    }

    #[Test]
    #[DataProvider('validatablesProvider')]
    public function it_preprocess_validatables($config, $input, $expected)
    {
        $fieldtype = $this->fieldtype($config);

        $value = $fieldtype->preProcessValidatable($input);

        if ($expected === null) {
            $this->assertNull($value);
        } else {
            $this->assertInstanceOf(Carbon::class, $value);
            $this->assertEquals($expected, $value->toIso8601ZuluString());
        }
    }

    public static function validatablesProvider()
    {
        // This only contains valid values. Invalid ones would throw a validation exception, tested in "it_validates" below.

        return [
            'null' => [
                [],
                null,
                null,
            ],
            'null date when not required' => [
                [],
                null,
                null,
            ],
            'valid date' => [
                [],
                '2012-01-29T00:00:00Z',
                '2012-01-29T00:00:00Z',
            ],
            'valid date and time' => [
                ['time_enabled' => true],
                '2012-01-29T13:00:00Z',
                '2012-01-29T13:00:00Z',
            ],
            'valid date and time with seconds' => [
                ['time_enabled' => true, 'time_seconds_enabled' => true],
                '2012-01-29T13:14:15Z',
                '2012-01-29T13:14:15Z',
            ],
            // A carbon instance would be passed in if it was already processed.
            // e.g. if it was nested inside a Replicator.
            'carbon instance' => [
                [],
                Carbon::parse('2012-01-29'),
                '2012-01-29T00:00:00Z',
            ],
        ];
    }

    #[Test]
    #[DataProvider('rangeValidatablesProvider')]
    public function it_preprocess_range_validatables($config, $input, $expected)
    {
        $fieldtype = $this->fieldtype($config);

        $value = $fieldtype->preProcessValidatable($input);

        if ($expected === null) {
            $this->assertNull($value);
        } else {
            $this->assertArrayHasKey('start', $value);
            $this->assertArrayHasKey('end', $value);
            $this->assertInstanceOf(Carbon::class, $start = $value['start']);
            $this->assertInstanceOf(Carbon::class, $end = $value['end']);
            $this->assertEquals($expected, [
                'start' => $start->toIso8601ZuluString(),
                'end' => $end->toIso8601ZuluString(),
            ]);
        }
    }

    public static function rangeValidatablesProvider()
    {
        // This only contains valid values. Invalid ones would throw a validation exception, tested in "it_validates" below.

        return [
            'null' => [
                ['mode' => 'range'],
                null,
                null,
            ],
            'valid date range' => [
                ['mode' => 'range'],
                ['start' => '2012-01-29T00:00:00Z', 'end' => '2012-01-30T00:00:00Z'],
                ['start' => '2012-01-29T00:00:00Z', 'end' => '2012-01-30T00:00:00Z'],
            ],
            // Start/end array with carbon instances would be passed in if it was already processed.
            // e.g. if it was nested inside a Replicator.
            'carbon instances' => [
                ['mode' => 'range'],
                ['start' => Carbon::parse('2012-01-29'), 'end' => Carbon::parse('2012-02-14')],
                [
                    'start' => '2012-01-29T00:00:00Z',
                    'end' => '2012-02-14T00:00:00Z',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function it_validates($config, $input, $expected)
    {
        $messages = [];
        $field = $this->fieldtype($config)->field();
        $fields = (new Fields)->setFields(collect([$field]))->addValues(['test' => $input]);

        try {
            $fields->validate();
        } catch (ValidationException $e) {
            $messages = $e->validator->errors()->all();
        }

        $this->assertEquals($expected, $messages);
    }

    public static function validationProvider()
    {
        return [
            'valid date' => [
                [],
                '2012-01-29T00:00:00Z',
                [],
            ],
            'null' => [
                [],
                null,
                [],
            ],
            'invalid date format' => [
                [],
                '2024-01-29',
                ['Not a valid date.'],
            ],
            'ridiculous invalid date format' => [
                [],
                'marchtember oneteenth',
                ['Not a valid date.'],
            ],
            'invalid date' => [
                [],
                '2010-06-50T00:00:00Z',
                ['Not a valid date.'],
            ],
            'valid date range' => [
                ['mode' => 'range'],
                ['start' => '2012-01-29T00:00:00Z', 'end' => '2012-01-30T00:00:00Z'],
                [],
            ],
            'null date in range mode required via validate' => [
                ['mode' => 'range', 'validate' => 'required'],
                null,
                ['The Test field is required.'],
            ],
            'missing start date' => [
                ['mode' => 'range'],
                ['end' => '2012-01-30T00:00:00Z'],
                ['Start date is required.'],
            ],
            'missing end date' => [
                ['mode' => 'range'],
                ['start' => '2012-01-29T00:00:00Z'],
                ['End date is required.'],
            ],
            'null start date' => [
                ['mode' => 'range'],
                ['start' => null, 'end' => '2012-01-30T00:00:00Z'],
                ['Start date is required.'],
            ],
            'null end date' => [
                ['mode' => 'range'],
                ['start' => '2012-01-29T00:00:00Z', 'end' => null],
                ['End date is required.'],
            ],
            'both dates null' => [
                ['mode' => 'range'],
                ['start' => null, 'end' => null],
                ['Date is required.'], // Invalid because if you want null, the whole value should be null.
            ],
            'both dates null, required via bool' => [
                ['mode' => 'range', 'required' => true],
                ['start' => null, 'end' => null],
                ['Date is required.'],
            ],
            'both dates null, required via validate' => [
                ['mode' => 'range', 'validate' => 'required'],
                ['start' => null, 'end' => null],
                ['Date is required.'],
            ],
            'invalid start date' => [
                ['mode' => 'range'],
                ['start' => '2010-06-50T00:00:00Z', 'end' => '2012-01-30T00:00:00Z'],
                ['Not a valid start date.'],
            ],
            'invalid end date' => [
                ['mode' => 'range'],
                ['start' => '2012-01-29T00:00:00Z', 'end' => '2010-06-50T00:00:00Z'],
                ['Not a valid end date.'],
            ],
            'invalid start date format' => [
                ['mode' => 'range'],
                ['start' => 'marchtember oneteenth', 'end' => '2012-01-30T00:00:00Z'],
                ['Not a valid start date.'],
            ],
            'invalid end date format' => [
                ['mode' => 'range'],
                ['start' => '2012-01-29T00:00:00Z', 'end' => 'marchtember oneteenth'],
                ['Not a valid end date.'],
            ],
        ];
    }

    public function fieldtype($config = [])
    {
        $field = new Field('test', array_replace([
            'type' => 'date',
            'mode' => 'single',
        ], $config));

        return (new Date)->setField($field);
    }
}
