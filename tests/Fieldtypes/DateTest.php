<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Preference;
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
    public function it_augments($config, $value, $expected)
    {
        $augmented = $this->fieldtype($config)->augment($value);

        $this->assertInstanceOf(Carbon::class, $augmented);
        $this->assertEquals($expected, $augmented->format('Y M d H:i:s'));
    }

    public static function augmentProvider()
    {
        return [
            'date' => [
                [],
                '2012-01-04',
                '2012 Jan 04 00:00:00',
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d'],
                '2012--01--04',
                '2012 Jan 04 00:00:00',
            ],

            // The time and seconds configs are important, otherwise
            // when when parsing dates without times, the time would inherit from "now".
            // We need to rely on the configs to know when or when not to reset the time.

            'date with time' => [
                ['time_enabled' => true],
                '2012-01-04 15:32',
                '2012 Jan 04 15:32:00',
            ],
            'date with time but seconds disabled' => [
                ['time_enabled' => true],
                '2012-01-04 15:32:54',
                '2012 Jan 04 15:32:00',
            ],
            'date with time and seconds' => [
                ['time_enabled' => true, 'time_seconds_enabled' => true],
                '2012-01-04 15:32:54',
                '2012 Jan 04 15:32:54',
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
    public function it_processes_on_save($config, $value, $expected)
    {
        $this->assertSame($expected, $this->fieldtype($config)->process($value));
    }

    public static function processProvider()
    {
        return [
            'null' => [
                [],
                null,
                null,
            ],
            'object with nulls' => [
                [],
                ['date' => null, 'time' => null],
                null,
            ],
            'object with missing time' => [
                [],
                ['date' => null],
                null,
            ],
            'date with default format' => [
                [],
                ['date' => '2012-08-29', 'time' => '00:00'],
                '2012-08-29',
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d H/i'],
                ['date' => '2012-08-29', 'time' => '00:00'],
                '2012--08--29 00/00',
            ],
            'date with missing time' => [
                [],
                ['date' => '2012-08-29'],
                '2012-08-29',
            ],
            'date with time' => [
                ['time_enabled' => true],
                ['date' => '2012-08-29', 'time' => '13:43'],
                '2012-08-29 13:43',
            ],
            'null range' => [
                ['mode' => 'range'],
                ['start' => null, 'end' => null],
                null,
            ],
            'range with default format' => [
                ['mode' => 'range'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '23:59']],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
            ],
            'range with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '23:59']],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 23/59'],
            ],
        ];
    }

    #[Test]
    public function it_saves_date_as_integer_if_format_results_in_a_number()
    {
        $this->assertSame(20120829, $this->fieldtype(['format' => 'Ymd'])->process(['date' => '2012-08-29', 'time' => null]));
    }

    #[Test]
    public function it_saves_ranges_as_integers_if_format_results_in_a_number()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'YmdHi']);

        $this->assertSame(
            ['start' => 201208290000, 'end' => 201309272359],
            $fieldtype->process(['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '23:59']])
        );
    }

    #[Test]
    #[DataProvider('preProcessProvider')]
    public function it_preprocesses($config, $value, $expected)
    {
        $this->assertSame($expected, $this->fieldtype($config)->preProcess($value));
    }

    public static function preProcessProvider()
    {
        return [
            'null' => [
                [],
                null,
                ['date' => null, 'time' => null],
            ],
            'now' => [
                [],
                'now', // this would happen if the value was null, but default was "now"
                ['date' => '2010-12-25', 'time' => '13:43'], // current date
            ],
            'date without time' => [
                [],
                '2012-08-29',
                ['date' => '2012-08-29', 'time' => '00:00'],
            ],
            'date with default format' => [
                [],
                '2012-08-29 00:00',
                ['date' => '2012-08-29', 'time' => '00:00'],
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d H/i'],
                '2012--08--29 00/00',
                ['date' => '2012-08-29', 'time' => '00:00'],
            ],
            'date with time' => [
                ['time_enabled' => true],
                '2012-08-29 13:43',
                ['date' => '2012-08-29', 'time' => '13:43'],
            ],
            'null range' => [
                ['mode' => 'range'],
                null,
                null,
            ],
            'range with default format' => [
                ['mode' => 'range'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '23:59']],
            ],
            'range with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 23/59'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '23:59']],
            ],
            'range where single date has been provided' => [
                // e.g. If it was once a non-range field.
                // Use the single date as both the start and end dates.
                ['mode' => 'range'],
                '2012-08-29',
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2012-08-29', 'time' => '23:59']],
            ],
            'range where single date has been provided with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d'],
                '2012--08--29',
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2012-08-29', 'time' => '23:59']],
            ],
            'date where range has been provided' => [
                // e.g. If it was once a range field. Use the start date.
                [],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 23:59'],
                ['date' => '2012-08-29', 'time' => '00:00'],
            ],
            'date where range has been provided with custom format' => [
                ['format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 23/59'],
                ['date' => '2012-08-29', 'time' => '00:00'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('preProcessIndexProvider')]
    public function it_preprocesses_for_index($config, $value, $expected)
    {
        // Show that the date format from the preference is being used, and
        // that the fall back would have been the configured date format.
        config(['statamic.cp.date_format' => 'custom']);
        Preference::shouldReceive('get')->with('date_format', 'custom')->andReturn('Y/m/d');

        $this->assertSame($expected, $this->fieldtype($config)->preProcessIndex($value));
    }

    public static function preProcessIndexProvider()
    {
        return [
            'null' => [
                [],
                null,
                null,
            ],
            'date with default format' => [
                [],
                '2012-08-29 00:00',
                ['date' => '2012-08-29', 'time' => '00:00', 'mode' => 'single', 'display_format' => 'YYYY/MM/DD'],
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d H/i'],
                '2012--08--29 00/00',
                ['date' => '2012-08-29', 'time' => '00:00', 'mode' => 'single', 'display_format' => 'YYYY/MM/DD'],
            ],
            'date with time' => [
                ['time_enabled' => true],
                '2012-08-29 13:43',
                ['date' => '2012-08-29', 'time' => '13:43', 'mode' => 'single', 'display_format' => 'YYYY/MM/DD HH:mm'],
            ],
            'date with time and custom format' => [
                ['time_enabled' => true, 'format' => 'Y--m--d H:i'],
                '2012--08--29 13:43',
                ['date' => '2012-08-29', 'time' => '13:43', 'mode' => 'single', 'display_format' => 'YYYY/MM/DD HH:mm'],
            ],
            'null range' => [
                ['mode' => 'range'],
                null,
                null,
            ],
            'range with default format' => [
                ['mode' => 'range'],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '00:00'], 'mode' => 'range', 'display_format' => 'YYYY/MM/DD'],
            ],
            'range with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 00/00'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '00:00'], 'mode' => 'range', 'display_format' => 'YYYY/MM/DD'],
            ],
            'range where single date has been provided' => [
                // e.g. If it was once a non-range field.
                // Use the single date as both the start and end dates.
                ['mode' => 'range'],
                '2012-08-29',
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2012-08-29', 'time' => '00:00'], 'mode' => 'range', 'display_format' => 'YYYY/MM/DD'],
            ],
            'range where single date has been provided with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d H/i'],
                '2012--08--29 00/00',
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2012-08-29', 'time' => '00:00'], 'mode' => 'range', 'display_format' => 'YYYY/MM/DD'],
            ],
            'date where range has been provided' => [
                // e.g. If it was once a range field. Use the start date.
                [],
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['date' => '2012-08-29', 'time' => '00:00', 'mode' => 'single', 'display_format' => 'YYYY/MM/DD'],
            ],
            'date where range has been provided with custom format' => [
                ['format' => 'Y--m--d H/i'],
                ['start' => '2012--08--29 00/00', 'end' => '2013--09--27 00/00'],
                ['date' => '2012-08-29', 'time' => '00:00', 'mode' => 'single', 'display_format' => 'YYYY/MM/DD'],
            ],
            'range where time has been enabled' => [
                ['mode' => 'range', 'time_enabled' => true], // enabling time should have no effect.
                ['start' => '2012-08-29 00:00', 'end' => '2013-09-27 00:00'],
                ['start' => ['date' => '2012-08-29', 'time' => '00:00'], 'end' => ['date' => '2013-09-27', 'time' => '00:00'], 'mode' => 'range', 'display_format' => 'YYYY/MM/DD'],
            ],
        ];
    }

    #[Test]
    public function it_gets_the_display_format_when_time_is_disabled()
    {
        $fieldtype = $this->fieldtype();

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    #[Test]
    public function it_gets_the_display_format_when_time_is_enabled()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true]);
        $fieldtype->field()->setValue('2013-04-01');

        $this->assertEquals('Y-m-d H:i', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    #[Test]
    public function it_gets_the_display_format_for_ranges()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range']);

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    #[Test]
    public function it_gets_the_display_format_when_time_is_disabled_with_custom_format()
    {
        $fieldtype = $this->fieldtype(['format' => 'U']);

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    #[Test]
    public function it_gets_the_display_format_when_time_is_enabled_with_custom_format()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true, 'format' => 'U']);

        $this->assertEquals('Y-m-d H:i', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    #[Test]
    public function it_gets_the_display_format_for_ranges_with_custom_format()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'U']);

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
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
            $this->assertEquals($expected, $value->format('Y-m-d H:i:s'));
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
                ['date' => null, 'time' => null],
                null,
            ],
            'valid date' => [
                [],
                ['date' => '2012-01-29', 'time' => null],
                '2012-01-29 00:00:00',
            ],
            'valid date and time' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => '13:00'],
                '2012-01-29 13:00:00',
            ],
            'valid date and time with seconds' => [
                ['time_enabled' => true, 'time_seconds_enabled' => true],
                ['date' => '2012-01-29', 'time' => '13:14:15'],
                '2012-01-29 13:14:15',
            ],
            'null time' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => null],
                '2012-01-29 00:00:00',
            ],
            // A carbon instance would be passed in if it was already processed.
            // e.g. if it was nested inside a Replicator.
            'carbon instance' => [
                [],
                Carbon::parse('2012-01-29'),
                '2012-01-29 00:00:00',
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
            $format = 'Y-m-d H:i:s';
            $this->assertEquals($expected, [
                'start' => $value['start']->format($format),
                'end' => $value['end']->format($format),
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
                ['date' => ['start' => '2012-01-29', 'end' => '2012-01-30']],
                [
                    'start' => '2012-01-29 00:00:00',
                    'end' => '2012-01-30 00:00:00',
                ],
            ],
            'null date in range mode' => [
                ['mode' => 'range'],
                ['date' => null],
                null,
            ],
            'both dates null' => [
                ['mode' => 'range'],
                ['date' => ['start' => null, 'end' => null]],
                null,
            ],
            // Start/end array with carbon instances would be passed in if it was already processed.
            // e.g. if it was nested inside a Replicator.
            'carbon instances' => [
                ['mode' => 'range'],
                ['start' => Carbon::parse('2012-01-29'), 'end' => Carbon::parse('2012-02-14')],
                [
                    'start' => '2012-01-29 00:00:00',
                    'end' => '2012-02-14 00:00:00',
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
                ['date' => '2012-01-29', 'time' => null],
                [],
            ],
            'null' => [
                [],
                null,
                [],
            ],
            'not an array' => [
                [],
                'a string',
                ['Must be an array.'],
            ],
            'missing date' => [
                [],
                [],
                ['Date is required.'],
            ],
            'null date when not required' => [
                [],
                ['date' => null, 'time' => null],
                [],
            ],
            'null required date via bool' => [
                ['required' => true],
                ['date' => null, 'time' => null],
                ['Date is required.'],
            ],
            'null required date via validate' => [
                ['validate' => 'required'],
                ['date' => null, 'time' => null],
                ['Date is required.'],
            ],
            'invalid date format' => [
                [],
                ['date' => 'marchtember oneteenth', 'time' => null],
                ['Not a valid date.'],
            ],
            'invalid date' => [
                [],
                ['date' => '2010-06-50'],
                ['Not a valid date.'],
            ],
            'valid date range' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2012-01-29', 'end' => '2012-01-30']],
                [],
            ],
            'null date in range mode' => [
                ['mode' => 'range'],
                ['date' => null],
                [],
            ],
            'null date in range mode required via bool' => [
                ['mode' => 'range', 'required' => true],
                ['date' => null],
                ['Date is required.'],
            ],
            'null date in range mode required via validate' => [
                ['mode' => 'range', 'validate' => 'required'],
                ['date' => null],
                ['Date is required.'],
            ],
            'missing start date' => [
                ['mode' => 'range'],
                ['date' => ['end' => '2012-01-30']],
                ['Start date is required.'],
            ],
            'missing end date' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2012-01-29']],
                ['End date is required.'],
            ],
            'null start date' => [
                ['mode' => 'range'],
                ['date' => ['start' => null, 'end' => '2012-01-30']],
                ['Start date is required.'],
            ],
            'null end date' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2012-01-29', 'end' => null]],
                ['End date is required.'],
            ],
            'both dates null' => [
                ['mode' => 'range'],
                ['date' => ['start' => null, 'end' => null]],
                [], // valid because not required
            ],
            'both dates null, required via bool' => [
                ['mode' => 'range', 'required' => true],
                ['date' => ['start' => null, 'end' => null]],
                ['Date is required.'],
            ],
            'both dates null, required via validate' => [
                ['mode' => 'range', 'validate' => 'required'],
                ['date' => ['start' => null, 'end' => null]],
                ['Date is required.'],
            ],
            'invalid start date' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2010-06-50', 'end' => '2012-01-30']],
                ['Not a valid start date.'],
            ],
            'invalid end date' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2012-01-29', 'end' => '2010-06-50']],
                ['Not a valid end date.'],
            ],
            'invalid start date format' => [
                ['mode' => 'range'],
                ['date' => ['start' => 'marchtember oneteenth', 'end' => '2012-01-30']],
                ['Not a valid start date.'],
            ],
            'invalid end date format' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2012-01-29', 'end' => 'marchtember oneteenth']],
                ['Not a valid end date.'],
            ],
            'valid date and time' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => '13:00'],
                [],
            ],
            'missing time' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29'],
                ['Time is required.'],
            ],
            'null time' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => null],
                [],
            ],
            'null required time via bool' => [
                ['time_enabled' => true, 'required' => true],
                ['date' => '2012-01-29', 'time' => null],
                ['Time is required.'],
            ],
            'null required time via validate' => [
                ['time_enabled' => true, 'validate' => 'required'],
                ['date' => '2012-01-29', 'time' => null],
                ['Time is required.'],
            ],
            'invalid time format' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => 'not formatted like a time'],
                ['Not a valid time.'],
            ],
            '12 hour time' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => '1:00'],
                ['Not a valid time.'],
            ],
            'invalid hour' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => '25:00'],
                ['Not a valid time.'],
            ],
            'invalid minute' => [
                ['time_enabled' => true],
                ['date' => '2012-01-29', 'time' => '14:65'],
                ['Not a valid time.'],
            ],
            'invalid second' => [
                ['time_enabled' => true, 'time_seconds_enabled' => true],
                ['date' => '2012-01-29', 'time' => '13:00:60'],
                ['Not a valid time.'],
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
