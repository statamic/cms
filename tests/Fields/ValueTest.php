<?php

namespace Tests\Fields;

use Statamic\Facades\Blueprint;
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

    /** @test */
    function it_converts_to_json_and_augments_child_values()
    {
        $fieldtype = new class extends Fieldtype {
            public function augment($data)
            {
                return array_map(function ($item) {
                    return is_string($item) ? strtoupper($item) . '!' : $item;
                }, $data);
            }
        };

        $fieldtypeTwo = new class extends Fieldtype {
            public function augment($data)
            {
                return new class($data) implements \Statamic\Contracts\Data\Augmentable {
                    use \Statamic\Data\Augmentable;
                    public function __construct($id) {
                        $this->id = $id;
                    }
                    public function blueprint()
                    {
                        return Blueprint::make();
                    }
                    public function augmentedArrayData()
                    {
                        return [
                            'id' => $this->id,
                            'title' => 'Title for '.$this->id,
                        ];
                    }
                };
            }
        };

        $value = new Value([
            'foo' => 'bar',
            'baz' => new Value('123', null, $fieldtypeTwo)
        ], null, $fieldtype);

        $this->assertEquals('{"foo":"BAR!","baz":{"id":"123","title":"Title for 123"}}', json_encode($value));
    }
}
