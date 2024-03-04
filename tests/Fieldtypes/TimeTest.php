<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Statamic\Fields\Field;
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

    public static function processProvider()
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

    /**
     * @test
     *
     * @dataProvider validationProvider
     */
    public function it_validates($config, $input, $expected)
    {
        $field = $this->fieldtype($config)->field();
        $messages = [];

        try {
            Validator::validate(['test' => $input], $field->rules(), [], $field->validationAttributes());
        } catch (ValidationException $e) {
            $messages = $e->validator->errors()->all();
        }

        $this->assertEquals($expected, $messages);
    }

    public static function validationProvider()
    {
        return [
            'valid time' => [
                [],
                '14:00',
                [],
            ],
            'valid time with seconds' => [
                ['seconds_enabled' => true],
                '14:00:00',
                [],
            ],
            'invalid time format' => [
                [],
                'not formatted like a time',
                ['Not a valid time.'],
            ],
            '12 hour time' => [
                [],
                '1:00',
                ['Not a valid time.'],
            ],
            'invalid hour' => [
                [],
                '25:00',
                ['Not a valid time.'],
            ],
            'invalid minute' => [
                [],
                '14:65',
                ['Not a valid time.'],
            ],
            'invalid second' => [
                ['seconds_enabled' => true],
                '13:00:60',
                ['Not a valid time.'],
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
