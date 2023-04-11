<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Carbon;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Date;
use Statamic\Fieldtypes\Time;
use Tests\TestCase;

class TimeTest extends TestCase
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
                null,
                null,
            ],
            'null with seconds' => [
                ['seconds_enabled' => true],
                null,
                null,
            ],
            'midnight' => [
                [],
                '00:00',
                '00:00',
            ],
            'time' => [
                [],
                '15:24',
                '15:24',
            ],
        ];
    }

    public function fieldtype($config = [])
    {
        $field = new Field('test', array_replace([
            'type' => 'time',
            'mode' => 'single',
        ], $config));

        return (new Time)->setField($field);
    }
}
