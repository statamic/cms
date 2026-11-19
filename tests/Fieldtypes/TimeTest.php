<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    #[DataProvider('validationProvider')]
    public function it_validates($config, $input, $passes)
    {
        $field = $this->fieldtype($config)->field();
        $messages = collect();

        try {
            Validator::validate(['test' => $input], $field->rules(), [], $field->validationAttributes());
        } catch (ValidationException $e) {
            $messages = $e->validator->errors();
        }

        if ($passes) {
            $this->assertCount(0, $messages);
        } else {
            $this->assertEquals(__('statamic::validation.time'), $messages->first());
        }
    }

    public static function validationProvider()
    {
        return [
            'valid time' => [
                [],
                '14:00',
                true,
            ],
            'valid time with seconds' => [
                ['seconds_enabled' => true],
                '14:00:00',
                true,
            ],
            'invalid time format' => [
                [],
                'not formatted like a time',
                false,
            ],
            '12 hour time' => [
                [],
                '1:00',
                false,
            ],
            'invalid hour' => [
                [],
                '25:00',
                false,
            ],
            'invalid minute' => [
                [],
                '14:65',
                false,
            ],
            'invalid second' => [
                ['seconds_enabled' => true],
                '13:00:60',
                false,
            ],
        ];
    }

    #[Test]
    #[DataProvider('augmentProvider')]
    public function it_augments($config, $value, $expected)
    {
        $this->assertSame($expected, $this->fieldtype($config)->augment($value));
    }

    public static function augmentProvider()
    {
        return [
            'null without format' => [
                [],
                null,
                null,
            ],
            'null with format' => [
                ['augment_format' => 'g:ia'],
                null,
                null,
            ],
            'time without format returns as-is' => [
                [],
                '14:30',
                '14:30',
            ],
            'time with format' => [
                ['augment_format' => 'g:ia'],
                '14:30',
                '2:30pm',
            ],
            'time with seconds and format' => [
                ['augment_format' => 'g:i:sa'],
                '14:30:45',
                '2:30:45pm',
            ],
        ];
    }

    #[Test]
    public function it_does_not_apply_timezone_when_augmenting()
    {
        config()->set('statamic.system.display_timezone', 'Europe/Berlin');
        config()->set('statamic.system.localize_dates_in_modifiers', true);

        $this->assertSame('2:30pm', $this->fieldtype(['augment_format' => 'g:ia'])->augment('14:30'));
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
