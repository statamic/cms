<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Carbon;
use Statamic\Facades\Preference;
use Statamic\Fields\Field;
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

    /**
     * @test
     *
     * @dataProvider augmentProvider
     */
    public function it_augments($config, $value, $expected)
    {
        $augmented = $this->fieldtype($config)->augment($value);

        $this->assertInstanceOf(Carbon::class, $augmented);
        $this->assertEquals($expected, $augmented->format('Y M d H:i:s'));
    }

    public function augmentProvider()
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

    /** @test */
    public function it_augments_null()
    {
        $augmented = $this->fieldtype()->augment(null);

        $this->assertNull($augmented);
    }

    /** @test */
    public function it_augments_a_carbon_instance()
    {
        // Could happen if you are using the date fieldtype to augment a manually provided value.

        $instance = new Carbon;
        $augmented = $this->fieldtype()->augment($instance);

        $this->assertSame($instance, $augmented);
    }

    /** @test */
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

    /** @test */
    public function it_augments_a_null_range()
    {
        $augmented = $this->fieldtype(['mode' => 'range'])->augment(null);

        $this->assertNull($augmented);
    }

    /**
     * @test
     *
     * @dataProvider processProvider
     */
    public function it_processes_on_save($config, $value, $expected)
    {
        $this->assertSame($expected, $this->fieldtype($config)->process($value));
    }

    public function processProvider()
    {
        return [
            'null' => [
                [],
                ['date' => null, 'time' => null],
                null,
            ],
            'date with default format' => [
                [],
                ['date' => '2012-08-29', 'time' => null],
                '2012-08-29',
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d'],
                ['date' => '2012-08-29', 'time' => null],
                '2012--08--29',
            ],
            'date with time' => [
                ['time_enabled' => true],
                ['date' => '2012-08-29', 'time' => '13:43'],
                '2012-08-29 13:43',
            ],
            'date with time and custom format' => [
                ['time_enabled' => true, 'format' => 'Y--m--d H:i'],
                ['date' => '2012-08-29', 'time' => '13:43'],
                '2012--08--29 13:43',
            ],
            'null range' => [
                ['mode' => 'range'],
                ['date' => null, 'time' => null],
                null,
            ],
            'range with default format' => [
                ['mode' => 'range'],
                ['date' => ['start' => '2012-08-29', 'end' => '2013-09-27'], 'time' => null],
                ['start' => '2012-08-29', 'end' => '2013-09-27'],
            ],
            'range with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d'],
                ['date' => ['start' => '2012-08-29', 'end' => '2013-09-27'], 'time' => null],
                ['start' => '2012--08--29', 'end' => '2013--09--27'],
            ],
        ];
    }

    /** @test */
    public function it_saves_date_as_integer_if_format_results_in_a_number()
    {
        $this->assertSame(20120829, $this->fieldtype(['format' => 'Ymd'])->process(['date' => '2012-08-29', 'time' => null]));
    }

    /** @test */
    public function it_saves_ranges_as_integers_if_format_results_in_a_number()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'Ymd']);

        $this->assertSame(
            ['start' => 20120829, 'end' => 20130927],
            $fieldtype->process(['date' => ['start' => '2012-08-29', 'end' => '2013-09-27']])
        );
    }

    /**
     * @test
     *
     * @dataProvider preProcessProvider
     */
    public function it_preprocesses($config, $value, $expected)
    {
        $this->assertSame($expected, $this->fieldtype($config)->preProcess($value));
    }

    public function preProcessProvider()
    {
        return [
            'null' => [
                [],
                null,
                ['date' => null, 'time' => null],
            ],
            'null when required with boolean' => [
                ['required' => true],
                null,
                ['date' => '2010-12-25', 'time' => null], // current date
            ],
            'null when required with boolean with time enabled' => [
                ['required' => true, 'time_enabled' => true],
                null,
                ['date' => '2010-12-25', 'time' => '13:43'], // current datetime
            ],
            'null when required with validation' => [
                ['validate' => ['required']],
                null,
                ['date' => '2010-12-25', 'time' => null], // current date
            ],
            'null when required with validation with time enabled' => [
                ['validate' => ['required'], 'time_enabled' => true],
                null,
                ['date' => '2010-12-25', 'time' => '13:43'], // current datetime
            ],
            'date with default format' => [
                [],
                '2012-08-29',
                ['date' => '2012-08-29', 'time' => null],
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d'],
                '2012--08--29',
                ['date' => '2012-08-29', 'time' => null],
            ],
            'date with time' => [
                ['time_enabled' => true],
                '2012-08-29 13:43',
                ['date' => '2012-08-29', 'time' => '13:43'],
            ],
            'date with time and custom format' => [
                ['time_enabled' => true, 'format' => 'Y--m--d H:i'],
                '2012--08--29 13:43',
                ['date' => '2012-08-29', 'time' => '13:43'],
            ],
            'null range' => [
                ['mode' => 'range'],
                null,
                ['date' => null, 'time' => null],
            ],
            'null range when required with boolean' => [
                ['mode' => 'range', 'required' => true],
                null,
                ['date' => ['start' => '2010-12-25', 'end' => '2010-12-25'], 'time' => null],
            ],
            'null range when required with validation' => [
                ['mode' => 'range', 'validate' => ['required']],
                null,
                ['date' => ['start' => '2010-12-25', 'end' => '2010-12-25'], 'time' => null],
            ],
            'range with default format' => [
                ['mode' => 'range'],
                ['start' => '2012-08-29', 'end' => '2013-09-27'],
                ['date' => ['start' => '2012-08-29', 'end' => '2013-09-27'], 'time' => null],
            ],
            'range with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d'],
                ['start' => '2012--08--29', 'end' => '2013--09--27'],
                ['date' => ['start' => '2012-08-29', 'end' => '2013-09-27'], 'time' => null],
            ],
            'range where single date has been provided' => [
                // e.g. If it was once a non-range field.
                // Use the single date as both the start and end dates.
                ['mode' => 'range'],
                '2012-08-29',
                ['date' => ['start' => '2012-08-29', 'end' => '2012-08-29'], 'time' => null],
            ],
            'range where single date has been provided with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d'],
                '2012--08--29',
                ['date' => ['start' => '2012-08-29', 'end' => '2012-08-29'], 'time' => null],
            ],
            'date where range has been provided' => [
                // e.g. If it was once a range field. Use the start date.
                [],
                ['start' => '2012-08-29', 'end' => '2013-09-27'],
                ['date' => '2012-08-29', 'time' => null],
            ],
            'date where range has been provided with custom format' => [
                ['format' => 'Y--m--d'],
                ['start' => '2012--08--29', 'end' => '2013--09--27'],
                ['date' => '2012-08-29', 'time' => null],
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider preProcessIndexProvider
     */
    public function it_preprocesses_for_index($config, $value, $expected)
    {
        // Show that the date format from the preference is being used, and
        // that the fall back would have been the configured date format.
        config(['statamic.cp.date_format' => 'custom']);
        Preference::shouldReceive('get')->with('date_format', 'custom')->andReturn('Y/m/d');

        $this->assertSame($expected, $this->fieldtype($config)->preProcessIndex($value));
    }

    public function preProcessIndexProvider()
    {
        return [
            'null' => [
                [],
                null,
                null,
            ],
            'date with default format' => [
                [],
                '2012-08-29',
                '2012/08/29',
            ],
            'date with custom format' => [
                ['format' => 'Y--m--d'],
                '2012--08--29',
                '2012/08/29',
            ],
            'date with time' => [
                ['time_enabled' => true],
                '2012-08-29 13:43',
                '2012/08/29 13:43',
            ],
            'date with time and custom format' => [
                ['time_enabled' => true, 'format' => 'Y--m--d H:i'],
                '2012--08--29 13:43',
                '2012/08/29 13:43',
            ],
            'null range' => [
                ['mode' => 'range'],
                null,
                null,
            ],
            'range with default format' => [
                ['mode' => 'range'],
                ['start' => '2012-08-29', 'end' => '2013-09-27'],
                '2012/08/29 - 2013/09/27',
            ],
            'range with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d'],
                ['start' => '2012--08--29', 'end' => '2013--09--27'],
                '2012/08/29 - 2013/09/27',
            ],
            'range where single date has been provided' => [
                // e.g. If it was once a non-range field.
                // Use the single date as both the start and end dates.
                ['mode' => 'range'],
                '2012-08-29',
                '2012/08/29 - 2012/08/29',
            ],
            'range where single date has been provided with custom format' => [
                ['mode' => 'range', 'format' => 'Y--m--d'],
                '2012--08--29',
                '2012/08/29 - 2012/08/29',
            ],
            'date where range has been provided' => [
                // e.g. If it was once a range field. Use the start date.
                [],
                ['start' => '2012-08-29', 'end' => '2013-09-27'],
                '2012/08/29',
            ],
            'date where range has been provided with custom format' => [
                ['format' => 'Y--m--d'],
                ['start' => '2012--08--29', 'end' => '2013--09--27'],
                '2012/08/29',
            ],
            'range where time has been enabled' => [
                ['mode' => 'range', 'time_enabled' => true], // enabling time should have no effect.
                ['start' => '2012-08-29', 'end' => '2013-09-27'],
                '2012/08/29 - 2013/09/27',
            ],
        ];
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_disabled()
    {
        $fieldtype = $this->fieldtype();

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_enabled()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true]);
        $fieldtype->field()->setValue('2013-04-01');

        $this->assertEquals('Y-m-d H:i', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_for_ranges()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range']);

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_disabled_with_custom_format()
    {
        $fieldtype = $this->fieldtype(['format' => 'U']);

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_enabled_with_custom_format()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true, 'format' => 'U']);

        $this->assertEquals('Y-m-d H:i', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_for_ranges_with_custom_format()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'U']);

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
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
