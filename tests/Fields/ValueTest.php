<?php

namespace Tests\Fields;

use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\TestCase;

class ValueTest extends TestCase
{
    /** @test */
    function it_converts_to_string_using_the_augmented_value()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($data)
            {
                return strtoupper($data) . '!';
            }
        };

        $value = new Value('test', null, $fieldtype);

        $this->assertEquals('TEST!', (string) $value);
    }

    /** @test */
    function it_converts_to_json_using_the_augmented_value()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($data)
            {
                return array_map(function ($item) {
                    return strtoupper($item) . '!';
                }, $data);
            }
        };

        $value = new Value(['foo' => 'bar', 'baz' => 'qux'], null, $fieldtype);

        $this->assertEquals('{"foo":"BAR!","baz":"QUX!"}', json_encode($value));
    }
}