<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Code;
use Tests\TestCase;

class CodeTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider processValues
     **/
    public function it_processes_values($isSelectable, $value, $expected)
    {
        $field = (new Code)->setField(new Field('test', [
            'type' => 'code',
            'mode_selectable' => $isSelectable,
        ]));

        $this->assertEquals($expected, $field->process($value));
    }

    public function processValues()
    {
        return [
            'selectable' => [true, ['code' => 'bar', 'mode' => 'htmlmixed'], ['code' => 'bar', 'mode' => 'htmlmixed']],
            'non selectable' => [false, ['code' => 'bar', 'mode' => 'htmlmixed'], 'bar'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider preProcessValues
     **/
    public function it_preprocesses_values($value, $expected)
    {
        $field = (new Code)->setField(new Field('test', ['type' => 'code']));

        $this->assertEquals($expected, $field->preProcess($value));
    }

    public function preProcessValues()
    {
        return [
            'string' => ['bar', ['code' => 'bar', 'mode' => 'htmlmixed']],
            'array' => [['code' => 'bar', 'mode' => 'htmlmixed'], ['code' => 'bar', 'mode' => 'htmlmixed']],
            'null' => [null, ['code' => null, 'mode' => 'htmlmixed']],
        ];
    }

    /** @test */
    public function it_doesnt_do_any_preprocessing_for_config()
    {
        $field = (new Code)->setField(new Field('test', ['type' => 'code']));

        $this->assertEquals('whatever', $field->preProcessConfig('whatever'));
    }
}
