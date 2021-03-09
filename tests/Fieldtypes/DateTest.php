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

    public function fieldtype($config = [])
    {
        $field = new Field('test', array_merge([
            'type' => 'date',
        ], $config));

        return (new Date)->setField($field);
    }
}
