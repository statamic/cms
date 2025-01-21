<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Text;
use Tests\TestCase;

class TextTest extends TestCase
{
    #[Test]
    #[DataProvider('processValuesProvider')]
    public function it_processes_values($mode, $values)
    {
        $field = (new Text)->setField(new Field('test', [
            'type' => 'text',
            'input_type' => $mode,
        ]));

        $this->assertSame($values[0], $field->process('test'));
        $this->assertSame($values[1], $field->process('3'));
        $this->assertSame($values[2], $field->process('3test'));
        $this->assertSame($values[3], $field->process('3.14'));
        $this->assertSame($values[4], $field->process(null));
    }

    public static function processValuesProvider()
    {
        return [
            'text' => ['text', ['test', '3', '3test', '3.14', null]],
            'number' => ['number', [0, 3, 3, 3.14, null]],
        ];
    }
}
