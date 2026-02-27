<?php

namespace Tests\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Statamic\Query\Builder;
use Statamic\View\Antlers\AntlersString;
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
    public function it_uses_array_access_with_string()
    {
        $val = new Value('foo');

        $this->assertFalse(isset($val['something']));
        $this->assertEquals('nope', $val['something'] ?? 'nope');
    }

    #[Test]
    public function it_uses_array_access_with_array()
    {
        $val = new Value([
            'a' => 'alfa',
            'b' => 'bravo',
        ]);

        $this->assertTrue(isset($val['a']));
        $this->assertFalse(isset($val['c']));
        $this->assertEquals('alfa', $val['a'] ?? 'nope');
        $this->assertEquals('nope', $val['c'] ?? 'nope');
    }

    #[Test]
    public function it_can_iterate_over_array()
    {
        $val = new Value([
            'a' => 'alfa',
            'b' => 'bravo',
        ]);

        $arr = [];

        foreach ($val as $key => $value) {
            $arr[$key] = $value;
        }

        $this->assertEquals([
            'a' => 'alfa',
            'b' => 'bravo',
        ], $arr);
    }

    #[Test]
    public function it_can_iterate_over_collection()
    {
        $val = new Value(collect([
            'a' => 'alfa',
            'b' => 'bravo',
        ]));

        $arr = [];

        foreach ($val as $key => $value) {
            $arr[$key] = $value;
        }

        $this->assertEquals([
            'a' => 'alfa',
            'b' => 'bravo',
        ], $arr);
    }

    #[Test]
    public function it_can_iterate_over_query_builder()
    {
        $builder = \Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(collect([
            'a' => 'alfa',
            'b' => 'bravo',
        ]));

        $val = new Value($builder);

        $arr = [];

        foreach ($val as $key => $value) {
            $arr[$key] = $value;
        }

        $this->assertEquals([
            'a' => 'alfa',
            'b' => 'bravo',
        ], $arr);
    }

    #[Test]
    public function it_can_iterate_over_values()
    {
        $val = new Value(new Values([
            'a' => 'alfa',
            'b' => 'bravo',
        ]));

        $arr = [];

        foreach ($val as $key => $value) {
            $arr[$key] = $value;
        }

        $this->assertEquals([
            'a' => 'alfa',
            'b' => 'bravo',
        ], $arr);
    }

    #[Test]
    public function it_can_check_isset_on_properties()
    {
        $val = new Value((object) [
            'a' => 'alfa',
            'b' => '',
            'c' => null,
        ]);

        $this->assertTrue(isset($val->a));
        $this->assertTrue(isset($val->b));
        $this->assertFalse(isset($val->c));
        $this->assertFalse(isset($val->d));
    }

    #[Test]
    public function it_can_check_emptiness_on_properties()
    {
        $val = new Value((object) [
            'a' => 'alfa',
            'b' => '',
            'c' => null,
        ]);

        $this->assertFalse(empty($val->a));
        $this->assertTrue(empty($val->b));
        $this->assertTrue(empty($val->c));
        $this->assertTrue(empty($val->d));
    }

    #[Test]
    public function it_can_proxy_methods_to_value()
    {
        // This is useful when the value is an object like an Entry, you could
        // do $value->slug(). Or for a LabeledValue you could do $value->label().

        $object = new class
        {
            public function bar()
            {
                return 'foo';
            }
        };

        $value = new Value($object);

        $this->assertEquals('foo', $value->bar());
    }

    #[Test]
    public function it_can_proxy_property_access_to_value()
    {
        // This is useful when the value is an object like an Entry, you could
        // do $value->slug.

        $object = new class
        {
            public $bar = 'foo';
        };

        $value = new Value($object);

        $this->assertEquals('foo', $value->bar);
        $this->assertEquals('nope', $value->baz ?? 'nope');
    }

    #[Test]
    public function it_parses_from_raw_string()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                // if we are being asked to augment an already parsed antlers string
                // then we return the correct value
                if ($data instanceof AntlersString) {
                    return 'augmented_value';
                }

                return 'not_augmented_value';
            }

            public function config(?string $key = null, $fallback = null)
            {
                if ($key == 'antlers') {
                    return true;
                }

                return parent::config($key, $fallback);
            }

            public function shouldParseAntlersFromRawString(): bool
            {
                return true;
            }
        };

        $value = new Value('raw_value', null, $fieldtype);
        $value = $value->antlersValue(app(\Statamic\Contracts\View\Antlers\Parser::class), []);

        $this->assertEquals('augmented_value', (string) $value);
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
