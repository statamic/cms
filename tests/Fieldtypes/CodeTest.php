<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Code;
use Tests\TestCase;

class CodeTest extends TestCase
{
    #[Test]
    #[DataProvider('processValuesProvider')]
    public function it_processes_values($isSelectable, $value, $expected)
    {
        $field = (new Code)->setField(new Field('test', [
            'type' => 'code',
            'mode_selectable' => $isSelectable,
        ]));

        $this->assertEquals($expected, $field->process($value));
    }

    public static function processValuesProvider()
    {
        return [
            'selectable' => [true, ['code' => 'bar', 'mode' => 'htmlmixed'], ['code' => 'bar', 'mode' => 'htmlmixed']],
            'non selectable' => [false, ['code' => 'bar', 'mode' => 'htmlmixed'], 'bar'],
        ];
    }

    #[Test]
    #[DataProvider('preProcessValuesProvider')]
    public function it_preprocesses_values($value, $expected)
    {
        $field = (new Code)->setField(new Field('test', ['type' => 'code']));

        $this->assertEquals($expected, $field->preProcess($value));
    }

    public static function preProcessValuesProvider()
    {
        return [
            'string' => ['bar', ['code' => 'bar', 'mode' => 'htmlmixed']],
            'array' => [['code' => 'bar', 'mode' => 'htmlmixed'], ['code' => 'bar', 'mode' => 'htmlmixed']],
            'null' => [null, ['code' => null, 'mode' => 'htmlmixed']],
        ];
    }

    #[Test]
    #[DataProvider('preProcessValidatableValuesProvider')]
    public function it_preprocesses_validatable_values($value, $expected)
    {
        $field = (new Code)->setField(new Field('test', ['type' => 'code']));

        $this->assertEquals($expected, $field->preProcessValidatable($value));
    }

    public static function preProcessValidatableValuesProvider()
    {
        return [
            'string' => ['bar', 'bar'],
            'null' => [null, null],
            'array with code' => [['code' => 'bar', 'mode' => 'htmlmixed'], 'bar'],
            'array without code' => [['code' => null, 'mode' => 'htmlmixed'], null],
        ];
    }

    #[Test]
    public function required_rule_fails_on_an_empty_code_field()
    {
        $fields = (new \Statamic\Fields\Fields)->setItems([[
            'handle' => 'test',
            'field' => ['type' => 'code', 'validate' => ['required']],
        ]]);

        $empty = (new \Statamic\Fields\Validator)->fields(
            $fields->addValues(['test' => ['code' => null, 'mode' => 'application/ld+json']])
        );

        $this->assertFalse($empty->validator()->passes());

        $filled = (new \Statamic\Fields\Validator)->fields(
            $fields->addValues(['test' => ['code' => 'bar', 'mode' => 'application/ld+json']])
        );

        $this->assertTrue($filled->validator()->passes());
    }

    #[Test]
    public function it_doesnt_do_any_preprocessing_for_config()
    {
        $field = (new Code)->setField(new Field('test', ['type' => 'code']));

        $this->assertEquals('whatever', $field->preProcessConfig('whatever'));
    }
}
