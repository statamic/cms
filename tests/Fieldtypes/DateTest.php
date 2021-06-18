<?php

namespace Tests\Fieldtypes;

use Carbon\Carbon;
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

    /** @test */
    public function it_augments_a_date()
    {
        $augmented = $this->fieldtype()->augment('2012-01-04');

        $this->assertInstanceOf(Carbon::class, $augmented);
        $this->assertEquals('2012 Jan 04 00:00', $augmented->format('Y M d H:i'));
    }

    /** @test */
    public function it_augments_a_datetime()
    {
        $augmented = $this->fieldtype()->augment('2012-01-04 15:32');

        $this->assertInstanceOf(Carbon::class, $augmented);
        $this->assertEquals('2012 Jan 04 15:32', $augmented->format('Y M d H:i'));
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

    /** @test */
    public function it_saves_nulls()
    {
        $this->assertNull($this->fieldtype()->process(null));
    }

    /** @test */
    public function it_saves_null_ranges()
    {
        $this->assertNull($this->fieldtype(['mode' => 'range'])->process(null));
    }

    /** @test */
    public function it_saves_dates_using_default_format()
    {
        $this->assertEquals('2012-08-29', $this->fieldtype()->process('2012-08-29'));
    }

    /** @test */
    public function it_saves_dates_using_custom_format()
    {
        $this->assertEquals('2012--08--29', $this->fieldtype(['format' => 'Y--m--d'])->process('2012-08-29'));
    }

    /** @test */
    public function it_saves_date_as_integer_if_format_results_in_a_number()
    {
        $this->assertSame(20120829, $this->fieldtype(['format' => 'Ymd'])->process('2012-08-29'));
    }

    /** @test */
    public function it_saves_ranges_using_default_format()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range']);

        $this->assertEquals(
            ['start' => '2012-08-29', 'end' => '2013-09-27'],
            $fieldtype->process(['start' => '2012-08-29', 'end' => '2013-09-27'])
        );
    }

    /** @test */
    public function it_saves_ranges_using_custom_formats()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'Y--m--d']);

        $this->assertEquals(
            ['start' => '2012--08--29', 'end' => '2013--09--27'],
            $fieldtype->process(['start' => '2012-08-29', 'end' => '2013-09-27'])
        );
    }

    /** @test */
    public function it_saves_ranges_as_integers_if_format_results_in_a_number()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'Ymd']);

        $this->assertSame(
            ['start' => 20120829, 'end' => 20130927],
            $fieldtype->process(['start' => '2012-08-29', 'end' => '2013-09-27'])
        );
    }

    /** @test */
    public function it_preprocesses_a_null()
    {
        $this->assertNull($this->fieldtype()->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_when_required_with_boolean()
    {
        $this->assertEquals('2010-12-25', $this->fieldtype(['required' => true])->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_when_required_with_boolean_with_time_enabled()
    {
        $this->assertEquals('2010-12-25 13:43', $this->fieldtype(['required' => true, 'time_enabled' => true])->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_when_required_with_validation()
    {
        $this->assertEquals('2010-12-25', $this->fieldtype(['validate' => ['required']])->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_when_required_with_validation_with_time_enabled()
    {
        $this->assertEquals('2010-12-25 13:43', $this->fieldtype(['validate' => ['required'], 'time_enabled' => true])->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_with_a_range()
    {
        $this->assertNull($this->fieldtype(['mode' => 'range'])->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_with_a_range_when_required_with_boolean()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'required' => true]);

        $this->assertEquals(['start' => '2010-12-25', 'end' => '2010-12-25'], $fieldtype->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_null_with_a_range_when_required_with_validation()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'validate' => ['required']]);

        $this->assertEquals(['start' => '2010-12-25', 'end' => '2010-12-25'], $fieldtype->preProcess(null));
    }

    /** @test */
    public function it_preprocesses_a_date()
    {
        $fieldtype = $this->fieldtype();

        $this->assertEquals('2021-05-19', $fieldtype->preProcess('2021-05-19'));
    }

    /** @test */
    public function it_preprocesses_a_date_with_time_enabled()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true]);

        $this->assertEquals('2021-05-19 23:45', $fieldtype->preProcess('2021-05-19 23:45'));
    }

    /** @test */
    public function it_preprocesses_a_date_with_a_custom_format()
    {
        $fieldtype = $this->fieldtype(['format' => 'Y--m--d']);

        $this->assertEquals('2021-05-19', $fieldtype->preProcess('2021--05--19'));
    }

    /** @test */
    public function it_preprocesses_a_date_with_a_custom_format_and_time_enabled()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true, 'format' => 'Y--m--d--H--i']);

        $this->assertEquals('2021-05-19 23:45', $fieldtype->preProcess('2021--05--19--23--45'));
    }

    /** @test */
    public function it_preprocesses_a_range()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range']);

        $this->assertEquals(
            ['start' => '2010-12-25', 'end' => '2013-11-25'],
            $fieldtype->preProcess(['start' => '2010-12-25', 'end' => '2013-11-25'])
        );
    }

    /** @test */
    public function it_preprocesses_a_range_with_a_custom_format()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'Y--m--d']);

        $this->assertEquals(
            ['start' => '2010-12-25', 'end' => '2013-11-25'],
            $fieldtype->preProcess(['start' => '2010--12--25', 'end' => '2013--11--25'])
        );
    }

    /** @test */
    public function it_preprocesses_a_range_where_a_single_date_has_been_provided()
    {
        // e.g. If it was once a non-range field.
        // Use the single date as both the start and end dates.

        $fieldtype = $this->fieldtype(['mode' => 'range']);

        $this->assertEquals(
            ['start' => '2010-12-25', 'end' => '2010-12-25'],
            $fieldtype->preProcess('2010-12-25')
        );
    }

    /** @test */
    public function it_preprocesses_a_range_where_a_single_date_has_been_provided_with_a_custom_format()
    {
        $fieldtype = $this->fieldtype(['mode' => 'range', 'format' => 'Y--m--d']);

        $this->assertEquals(
            ['start' => '2010-12-25', 'end' => '2010-12-25'],
            $fieldtype->preProcess('2010--12--25')
        );
    }

    /** @test */
    public function it_preprocesses_a_date_where_a_range_has_been_provided()
    {
        // e.g. If it was once a range field. Use the start date.

        $fieldtype = $this->fieldtype();

        $this->assertEquals(
            '2010-12-25',
            $fieldtype->preProcess(['start' => '2010-12-25', 'end' => '2013-11-25'])
        );
    }

    /** @test */
    public function it_preprocesses_a_date_where_a_range_has_been_provided_with_a_custom_format()
    {
        $fieldtype = $this->fieldtype(['format' => 'Y--m--d']);

        $this->assertEquals(
            '2010-12-25',
            $fieldtype->preProcess(['start' => '2010--12--25', 'end' => '2013--11--25'])
        );
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_disabled()
    {
        $fieldtype = $this->fieldtype();

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_enabled_but_theres_no_time_selected()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true]);
        $fieldtype->field()->setValue('2013-04-01');

        $this->assertEquals('Y-m-d', $fieldtype->indexDisplayFormat());
        $this->assertEquals('Y-m-d', $fieldtype->fieldDisplayFormat());
    }

    /** @test */
    public function it_gets_the_display_format_when_time_is_enabled_and_a_time_has_been_selected()
    {
        $fieldtype = $this->fieldtype(['time_enabled' => true]);
        $fieldtype->field()->setValue('2013-04-01 19:45');

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
        $field = new Field('test', array_merge([
            'type' => 'date',
        ], $config));

        return (new Date)->setField($field);
    }
}
