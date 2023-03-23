<?php

namespace Tests\Fields;

use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\TestCase;

class ValueTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider isRelationshipFieldtypeProvider
     **/
    public function it_gets_whether_its_a_relationship_through_the_fieldtype($isRelationship, $fieldtype)
    {
        $value = new Value('test', null, $fieldtype);

        $this->assertEquals($isRelationship, $value->isRelationship());
    }

    public function isRelationshipFieldtypeProvider()
    {
        return [
            'relationship' => [true, new class extends Fieldtype
            {
                protected $relationship = true;
            }, ],
            'not a relationship' => [false, new class extends Fieldtype
            {
                protected $relationship = false;
            }, ],
            'no fieldtype' => [false, null],
        ];
    }

    /** @test */
    public function it_augments_through_the_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return strtoupper($data).'!';
            }

            public function shallowAugment($data)
            {
                return $data.' shallow';
            }
        };

        $value = new Value('test', null, $fieldtype);

        $this->assertEquals('TEST!', $value->value());
    }

    /** @test */
    public function it_shallow_augments_through_the_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return strtoupper($data).'!';
            }

            public function shallowAugment($data)
            {
                return $data.' shallow';
            }
        };

        $value = new Value('test', null, $fieldtype);

        $this->assertNotSame($value, $value->shallow());
        $this->assertInstanceOf(Value::class, $value->shallow());
        $this->assertEquals('test shallow', $value->shallow()->value());
    }

    /** @test */
    public function it_converts_to_string_using_the_augmented_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return strtoupper($data).'!';
            }
        };

        $value = new Value('test', null, $fieldtype);

        $this->assertEquals('TEST!', (string) $value);
    }

    /** @test */
    public function it_converts_to_json_using_the_augmented_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return array_map(function ($item) {
                    return strtoupper($item).'!';
                }, $data);
            }
        };

        $value = new Value(['foo' => 'bar', 'baz' => 'qux'], null, $fieldtype);

        $this->assertEquals('{"foo":"BAR!","baz":"QUX!"}', json_encode($value));
    }

    /** @test */
    public function it_converts_to_json_and_augments_child_values()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return array_map(function ($item) {
                    return is_string($item) ? strtoupper($item).'!' : $item;
                }, $data);
            }
        };

        $fieldtypeTwo = new class extends Fieldtype
        {
            public function augment($data)
            {
                return new DummyAugmentable($data);
            }
        };

        $fieldtypeThree = new class extends Fieldtype
        {
            public function augment($data)
            {
                return collect($data)->map(function ($id) {
                    return new DummyAugmentable($id);
                });
            }
        };

        $value = new Value([
            'foo' => 'bar',
            'baz' => new Value('123', null, $fieldtypeTwo),
            'qux' => new Value(['456', '789'], null, $fieldtypeThree),
        ], null, $fieldtype);

        $this->assertEquals('{"foo":"BAR!","baz":{"id":"123","title":"Title for 123"},"qux":[{"id":"456","title":"Title for 456"},{"id":"789","title":"Title for 789"}]}', json_encode($value));
    }
}

class DummyAugmentable implements \Statamic\Contracts\Data\Augmentable
{
    use \Statamic\Data\HasAugmentedData;

    private $id;

    public function __construct($id)
    {
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
}
