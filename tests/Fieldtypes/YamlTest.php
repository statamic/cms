<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\Fieldtypes\Yaml;
use Tests\TestCase;

class YamlTest extends TestCase
{
    /**
     * @test
     */
    public function it_converts_single_array_items_to_arrays()
    {
        $field = (new Yaml)->setField(new Field('test', [
            'type' => 'yaml'
        ]));

        $expected = [
            'one'
        ];

        $this->assertSame($expected, $field->process('- one'));
    }
}