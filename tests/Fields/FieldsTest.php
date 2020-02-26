<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldsetRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Fields\Validator;

class FieldsTest extends TestCase
{
    /** @test */
    function it_converts_to_a_collection()
    {
        $fields = new Fields;

        tap($fields->all(), function ($items) {
            $this->assertInstanceOf(Collection::class, $items);
            $this->assertCount(0, $items);
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'text']);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_one', ['type' => 'textarea']);
            });

        FieldsetRepository::shouldReceive('find')
            ->with('fieldset_three')
            ->andReturnUsing(function () {
                return (new Fieldset)->setHandle('fieldset_three')->setContents(['fields' => [
                    'foo' => ['type' => 'textarea'],
                    'bar' => ['type' => 'text'],
                ]]);
            });

        $fields->setItems([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ],
            [
                'handle' => 'three',
                'field' => [
                    'type' => 'textarea',
                ]
            ],
            [
                'import' => 'fieldset_three',
                'prefix' => 'a_',
            ],
            [
                'import' => 'fieldset_three',
                'prefix' => 'b_',
            ]
        ]);

        tap($fields->all(), function ($items) {
            $this->assertCount(7, $items);
            $this->assertEveryItemIsInstanceOf(Field::class, $items);
            $handles = ['one', 'two', 'three', 'a_foo', 'a_bar', 'b_foo', 'b_bar'];
            $this->assertEquals($handles, $items->map->handle()->values()->all());
            $this->assertEquals($handles, $items->keys()->all());
            $this->assertEquals(['text', 'textarea', 'textarea', 'textarea', 'text', 'textarea', 'text'], $items->map->type()->values()->all());
        });
    }

    /** @test */
    function it_gets_a_field_in_a_fieldset_when_given_a_reference()
    {
        $existing = new Field('bar', [
            'type' => 'textarea',
            'var_one' => 'one',
            'var_two' => 'two',
        ]);

        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturn($existing);

        $fields = (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar',
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(1, $fields);
        $field = $fields[0];
        $this->assertEquals('test', $field->handle());
        $this->assertEquals([
            'type' => 'textarea',
            'var_one' => 'one',
            'var_two' => 'two',
        ], $field->config());
    }

    /** @test */
    function it_can_override_the_config_in_a_referenced_field()
    {
        $existing = new Field('bar', [
            'type' => 'textarea',
            'var_one' => 'one',
            'var_two' => 'two',
        ]);

        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturn($existing);

        $fields = (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar',
            'config' => [
                'var_one' => 'overridden'
            ]
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(1, $fields);
        $field = $fields[0];
        $this->assertEquals('test', $field->handle());
        $this->assertEquals([
            'type' => 'textarea',
            'var_one' => 'overridden',
            'var_two' => 'two',
        ], $field->config());
    }

    /** @test */
    function it_throws_an_exception_when_an_invalid_field_reference_is_encountered()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Field foo.bar not found.');
        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturnNull();

        (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar'
        ]);
    }

    /** @test */
    function it_imports_the_fields_from_an_entire_fieldset_inline()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                'one' => [
                    'type' => 'text'
                ],
                'two' => [
                    'type' => 'textarea'
                ]
            ]
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $fields = (new Fields)->createFields([
            'import' => 'partial',
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(2, $fields);
        $this->assertEquals('one', $fields['one']->handle());
        $this->assertEquals('two', $fields['two']->handle());
    }

    /** @test */
    function it_prefixes_the_handles_of_imported_fieldsets()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                'one' => [
                    'type' => 'text'
                ],
                'two' => [
                    'type' => 'textarea'
                ]
            ]
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $fields = (new Fields)->createFields([
            'import' => 'partial',
            'prefix' => 'test_',
        ]);

        $this->assertTrue(is_array($fields));
        $this->assertCount(2, $fields);
        $this->assertEquals('test_one', $fields['test_one']->handle());
        $this->assertEquals('test_two', $fields['test_two']->handle());
    }

    /** @test */
    function it_throws_exception_when_trying_to_import_a_non_existent_fieldset()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Fieldset test_partial not found.');
        FieldsetRepository::shouldReceive('find')->with('test_partial')->once()->andReturnNull();

        (new Fields)->createFields([
            'import' => 'test_partial'
        ]);
    }

    /** @test */
    function it_checks_if_a_given_field_exists()
    {
        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => []
            ]
        ]);

        $this->assertTrue($fields->has('one'));
        $this->assertFalse($fields->has('two'));
    }

    /** @test */
    function it_gets_a_given_field()
    {
        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => ['display' => 'First']
            ]
        ]);

        $this->assertInstanceOf(Field::class, $field = $fields->get('one'));
        $this->assertEquals('First', $field->display());
        $this->assertNull($fields->get('two'));
    }

    /** @test */
    function it_gets_all_fields_except()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['display' => 'First']],
            ['handle' => 'two', 'field' => ['display' => 'Second']],
            ['handle' => 'three', 'field' => ['display' => 'Third']],
        ]);

        $this->assertInstanceOf(Fields::class, $fields->except('two'));
        $this->assertEquals(['one', 'three'], $fields->except('two')->all()->keys()->all());
        $this->assertEquals(['one'], $fields->except('two', 'three')->all()->keys()->all());
        $this->assertEquals(['three'], $fields->except(['one', 'two'])->all()->keys()->all());
    }

    /** @test */
    function it_gets_only_specific_fields()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['display' => 'First']],
            ['handle' => 'two', 'field' => ['display' => 'Second']],
            ['handle' => 'three', 'field' => ['display' => 'Third']],
        ]);

        $this->assertInstanceOf(Fields::class, $fields->only('two'));
        $this->assertEquals(['two'], $fields->only('two')->all()->keys()->all());
        $this->assertEquals(['two', 'three'], $fields->only('two', 'three')->all()->keys()->all());
        $this->assertEquals(['one', 'two'], $fields->only(['one', 'two'])->all()->keys()->all());
    }

    /** @test */
    function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturnUsing(function () {
                return new Field('field_one', [
                    'type' => 'text',
                    'display' => 'One',
                    'instructions' => 'One instructions',
                    'validate' => 'required|min:2',
                ]);
            });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturnUsing(function () {
                return new Field('field_two', [
                    'type' => 'textarea',
                    'display' => 'Two',
                    'instructions' => 'Two instructions',
                    'validate' => 'min:2'
                ]);
            });

        $fields = new Fields([
            'one' => [ // use keys to ensure they get stripped out
                'handle' => 'one',
                'field' => 'fieldset_one.field_one'
            ],
            'two' => [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two'
            ]
        ]);

        $this->assertEquals([
            [
                'handle' => 'one',
                'type' => 'text',
                'display' => 'One',
                'instructions' => 'One instructions',
                'required' => true,
                'validate' => 'required|min:2',
                'component' => 'text',
                'placeholder' => null,
                'character_limit' => 0,
                'input_type' => 'text',
                'prepend' => null,
                'append' => null,
            ],
            [
                'handle' => 'two',
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'required' => false,
                'validate' => 'min:2',
                'character_limit' => null,
                'component' => 'textarea',
            ]
        ], $fields->toPublishArray());
    }

    /** @test */
    function it_adds_values_to_fields()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', []);
        });

        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', []);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $return = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertNotSame($fields->get('one'), $return->get('one'));
        $this->assertEquals(['one' => 'foo', 'two' => 'bar'], $return->values()->all());
    }

    /** @test */
    function it_processes_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function process($data) {
                return $data . ' processed';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $processed = $fields->process();

        $this->assertNotSame($fields, $processed);
        $this->assertEquals([
            'one' => 'foo',
            'two' => 'bar'
        ], $fields->values()->all());
        $this->assertEquals([
            'one' => 'foo processed',
            'two' => 'bar processed'
        ], $processed->values()->all());
    }

    /** @test */
    function it_preprocesses_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function preProcess($data) {
                return $data . ' preprocessed';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $preProcessed = $fields->preProcess();

        $this->assertNotSame($fields, $preProcessed);
        $this->assertEquals([
            'one' => 'foo',
            'two' => 'bar'
        ], $fields->values()->all());
        $this->assertEquals([
            'one' => 'foo preprocessed',
            'two' => 'bar preprocessed'
        ], $preProcessed->values()->all());
    }

    /** @test */
    function it_augments_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function augment($data) {
                return $data . ' augmented';
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $augmented = $fields->augment();

        $this->assertNotSame($fields, $augmented);
        $this->assertEquals([
            'one' => 'foo',
            'two' => 'bar'
        ], $fields->values()->all());
        $this->assertEquals([
            'one' => 'foo augmented',
            'two' => 'bar augmented'
        ], $augmented->values()->all());
    }

    /** @test */
    function it_gets_meta_data_from_all_fields()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype {
            public function preload() {
                return 'meta data from field ' . $this->field->handle() . ' is ' . ($this->field->value() * 2);
            }
        });

        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'fieldtype']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'fieldtype']);
        });

        $fields = (new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two']
        ]))->addValues(['one' => 10, 'two' => 20]);

        $this->assertEquals([
            'one' => 'meta data from field one is 20',
            'two' => 'meta data from field two is 40',
        ], $fields->meta()->all());
    }

    /** @test */
    function it_filters_down_to_localizable_fields()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['type' => 'text', 'localizable' => false]],
            ['handle' => 'two', 'field' => ['type' => 'text', 'localizable' => false]],
            ['handle' => 'three', 'field' => ['type' => 'text', 'localizable' => true]],
        ]);

        $this->assertEquals(
            ['one', 'two', 'three'],
            $fields->all()->keys()->all()
        );

        $this->assertEquals(
            ['one', 'two'],
            $fields->unlocalizable()->all()->keys()->all()
        );

        $this->assertEquals(
            ['three'],
            $fields->localizable()->all()->keys()->all()
        );
    }

    /** @test */
    function it_gets_a_validator()
    {
        $fields = new Fields;
        Validator::shouldReceive('make')->once()->andReturnSelf();
        $mock = Validator::shouldReceive('fields')->once()->andReturnSelf()->getMock();

        $this->assertEquals($mock, $fields->validator());
    }

    /** @test */
    function it_validates_immediately()
    {
        $fields = new Fields;
        Validator::shouldReceive('make')->once()->andReturnSelf();
        Validator::shouldReceive('fields')->once()->andReturnSelf();
        Validator::shouldReceive('withRules')->with([])->once()->andReturnSelf();
        Validator::shouldReceive('validate')->once();

        $fields->validate();
    }

    /** @test */
    function it_validates_immediately_with_extra_rules()
    {
        $fields = new Fields;
        Validator::shouldReceive('make')->once()->andReturnSelf();
        Validator::shouldReceive('fields')->once()->andReturnSelf();
        Validator::shouldReceive('withRules')->with(['foo' => 'bar'])->once()->andReturnSelf();
        Validator::shouldReceive('validate')->once();

        $fields->validate(['foo' => 'bar']);
    }
}
