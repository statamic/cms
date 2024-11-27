<?php

namespace Tests\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Tests\TestCase;

class ValueTest extends TestCase
{
    #[Test]
    #[DataProvider('isRelationshipFieldtypeProvider')]
    public function it_gets_whether_its_a_relationship_through_the_fieldtype($isRelationship, $fieldtype)
    {
        $value = new Value('test', null, $fieldtype);

        $this->assertEquals($isRelationship, $value->isRelationship());
    }

    public static function isRelationshipFieldtypeProvider()
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_uses_the_default_value()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return $value.'!';
            }
        };

        $fieldtype->setField(new Field('search_engine_url', ['default' => 'https://google.com']));

        tap(new Value(null, null, $fieldtype), function ($value) {
            $this->assertNull($value->raw());
            $this->assertEquals('https://google.com!', $value->value());
        });

        tap(new Value('foo', null, $fieldtype), function ($value) {
            $this->assertEquals('foo', $value->raw());
            $this->assertEquals('foo!', $value->value());
        });
    }

    #[Test]
    public function it_does_not_use_the_default_when_returning_falsey_values()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return $value;
            }
        };

        $fieldtype->setField(new Field('the_handle', ['default' => true]));

        tap(new Value(false, null, $fieldtype), function ($value) {
            $this->assertSame(false, $value->value());
        });
    }

    #[Test]
    public function falsey_values_can_be_used_as_the_default()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return $value;
            }
        };

        $fieldtype->setField(new Field('the_handle', ['default' => false]));

        tap(new Value(null, null, $fieldtype), function ($value) {
            $this->assertSame(false, $value->value());
        });
    }

    #[Test]
    public function it_can_iterate()
    {
        $value = new Value(['alfa' => 'one', 'bravo' => 'two']);

        $result = [];

        foreach ($value as $key => $item) {
            $result[] = $key.','.$item;
        }

        $this->assertEquals(['alfa,one', 'bravo,two'], $result);
    }

    #[Test]
    public function it_can_iterate_if_value_is_already_iterable()
    {
        $value = new Value(
            collect(['alfa' => 'one', 'bravo' => 'two'])
        );

        $result = [];

        foreach ($value as $key => $item) {
            $result[] = $key.','.$item;
        }

        $this->assertEquals(['alfa,one', 'bravo,two'], $result);
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
