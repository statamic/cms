<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Fields\Validator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\FieldsetRecursionException;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Tests\TestCase;

class FieldsTest extends TestCase
{
    #[Test]
    public function it_converts_to_a_collection()
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
                    ['handle' => 'foo', 'field' => ['type' => 'textarea']],
                    ['handle' => 'bar', 'field' => ['type' => 'text']],
                ]]);
            });

        $fields->setItems([
            [
                'handle' => 'one',
                'field' => 'fieldset_one.field_one',
            ],
            [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two',
            ],
            [
                'handle' => 'three',
                'field' => [
                    'type' => 'textarea',
                ],
            ],
            [
                'import' => 'fieldset_three',
                'prefix' => 'a_',
            ],
            [
                'import' => 'fieldset_three',
                'prefix' => 'b_',
            ],
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

    #[Test]
    public function it_gets_a_field_in_a_fieldset_when_given_a_reference()
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

    #[Test]
    public function it_can_override_the_config_in_a_referenced_field()
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
                'var_one' => 'overridden',
            ],
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

    #[Test]
    public function it_throws_an_exception_when_an_invalid_field_reference_is_encountered()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Field foo.bar not found.');
        FieldRepository::shouldReceive('find')->with('foo.bar')->once()->andReturnNull();

        (new Fields)->createFields([
            'handle' => 'test',
            'field' => 'foo.bar',
        ]);
    }

    #[Test]
    public function it_imports_the_fields_from_an_entire_fieldset_inline()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'two',
                    'field' => ['type' => 'textarea'],
                ],
            ],
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

    #[Test]
    public function it_prefixes_the_handles_of_imported_fieldsets()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'two',
                    'field' => ['type' => 'textarea'],
                ],
            ],
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

    /**
     * @see https://github.com/statamic/cms/issues/2869
     **/
    #[Test]
    public function it_prefixes_the_handles_of_nested_imported_fieldsets()
    {
        $outer = (new Fieldset)->setHandle('outer')->setContents([
            'fields' => [
                [
                    'import' => 'inner',
                    'prefix' => 'prefix_',
                ],
            ],
        ]);

        $inner = (new Fieldset)->setHandle('inner')->setContents([
            'fields' => [
                [
                    'handle' => 'foo',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'bar',
                    'field' => ['type' => 'text'],
                ],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('outer')->times(2)->andReturn($outer);
        FieldsetRepository::shouldReceive('find')->with('inner')->times(1)->andReturn($inner);

        $fields = new Fields([
            [
                'import' => 'outer',
                'prefix' => 'first_',
            ],
            [
                'import' => 'outer',
                'prefix' => 'second_',
            ],
        ]);

        $fields = $fields->all();

        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertCount(4, $fields);
        $this->assertEquals('first_prefix_foo', $fields['first_prefix_foo']->handle());
        $this->assertEquals('first_prefix_bar', $fields['first_prefix_bar']->handle());
        $this->assertEquals('second_prefix_foo', $fields['second_prefix_foo']->handle());
        $this->assertEquals('second_prefix_bar', $fields['second_prefix_bar']->handle());
    }

    #[Test]
    public function it_throws_exception_when_trying_to_import_a_non_existent_fieldset()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Fieldset [test_partial] not found');
        FieldsetRepository::shouldReceive('find')->with('test_partial')->once()->andReturnNull();

        (new Fields)->createFields([
            'import' => 'test_partial',
        ]);
    }

    #[Test]
    public function it_can_override_the_config_for_fields_in_an_imported_fieldset()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text', 'foo' => 'original'],
                ],
                [
                    'handle' => 'two',
                    'field' => ['type' => 'textarea', 'foo' => 'original'],
                ],
                [
                    'handle' => 'three',
                    'field' => ['type' => 'textarea', 'foo' => 'original'],
                ],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $fields = (new Fields)->createFields([
            'import' => 'partial',
            'config' => [
                'one' => ['foo' => 'custom'],
                'three' => ['foo' => 'another custom'],
            ],
            // use a prefix to make sure they work together, without needing to write an almost identical test
            'prefix' => 'prefix_',
        ]);

        $this->assertEquals(['type' => 'text', 'foo' => 'custom'], $fields['prefix_one']->config());
        $this->assertEquals(['type' => 'textarea', 'foo' => 'original'], $fields['prefix_two']->config());
        $this->assertEquals(['type' => 'textarea', 'foo' => 'another custom'], $fields['prefix_three']->config());
    }

    #[Test]
    public function it_checks_if_a_given_field_exists()
    {
        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => [],
            ],
        ]);

        $this->assertTrue($fields->has('one'));
        $this->assertFalse($fields->has('two'));
    }

    #[Test]
    public function it_gets_a_given_field()
    {
        $fields = new Fields([
            [
                'handle' => 'one',
                'field' => ['display' => 'First'],
            ],
        ]);

        $this->assertInstanceOf(Field::class, $field = $fields->get('one'));
        $this->assertEquals('First', $field->display());
        $this->assertNull($fields->get('two'));
    }

    #[Test]
    public function it_gets_all_fields_except()
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

    #[Test]
    public function it_gets_only_specific_fields()
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

    #[Test]
    public function converts_to_array_suitable_for_rendering_fields_in_publish_component()
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
                    'validate' => 'min:2',
                ]);
            });

        $fields = new Fields([
            'one' => [ // use keys to ensure they get stripped out
                'handle' => 'one',
                'field' => 'fieldset_one.field_one',
            ],
            'two' => [
                'handle' => 'two',
                'field' => 'fieldset_one.field_two',
            ],
        ]);

        $this->assertEquals([
            [
                'handle' => 'one',
                'prefix' => null,
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
                'antlers' => false,
                'default' => null,
                'visibility' => 'visible',
                'read_only' => false, // deprecated
                'always_save' => false,
                'autocomplete' => null,
                'hide_display' => false,
                'instructions_position' => 'above',
                'listable' => 'hidden',
                'sortable' => true,
                'replicator_preview' => true,
                'duplicate' => true,
                'revisable' => true,
            ],
            [
                'handle' => 'two',
                'prefix' => null,
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'required' => false,
                'validate' => 'min:2',
                'character_limit' => null,
                'component' => 'textarea',
                'antlers' => false,
                'placeholder' => null,
                'default' => null,
                'visibility' => 'visible',
                'read_only' => false, // deprecated
                'always_save' => false,
                'hide_display' => false,
                'instructions_position' => 'above',
                'listable' => 'hidden',
                'sortable' => true,
                'replicator_preview' => true,
                'duplicate' => true,
                'revisable' => true,
            ],
        ], $fields->toPublishArray());
    }

    #[Test]
    public function converts_to_array_suitable_for_rendering_prefixed_conditional_fields_in_publish_component()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('deeper_partial')
            ->andReturn((new Fieldset)->setHandle('deeper_partial')->setContents([
                'title' => 'Deeper Partial',
                'fields' => [
                    [
                        'handle' => 'two',
                        'field' => ['type' => 'text'],
                    ],
                ],
            ]));

        FieldsetRepository::shouldReceive('find')
            ->with('partial')
            ->andReturn((new Fieldset)->setHandle('partial')->setContents([
                'title' => 'Partial',
                'fields' => [
                    [
                        'handle' => 'one',
                        'field' => ['type' => 'text'],
                    ],
                    [
                        'import' => 'deeper_partial',
                        'prefix' => 'deeper_',
                    ],
                ],
            ]));

        $fields = new Fields([
            ['import' => 'partial', 'prefix' => 'nested_'],
        ]);

        $this->assertEquals([
            [
                'handle' => 'nested_one',
                'prefix' => 'nested_',
                'type' => 'text',
                'display' => 'Nested One',
                'placeholder' => null,
                'input_type' => 'text',
                'character_limit' => 0,
                'prepend' => null,
                'append' => null,
                'component' => 'text',
                'instructions' => null,
                'required' => false,
                'antlers' => false,
                'default' => null,
                'visibility' => 'visible',
                'read_only' => false, // deprecated
                'always_save' => false,
                'autocomplete' => null,
                'hide_display' => false,
                'instructions_position' => 'above',
                'listable' => 'hidden',
                'sortable' => true,
                'replicator_preview' => true,
                'duplicate' => true,
                'revisable' => true,
            ],
            [
                'handle' => 'nested_deeper_two',
                'prefix' => 'nested_deeper_',
                'type' => 'text',
                'display' => 'Nested Deeper Two',
                'placeholder' => null,
                'input_type' => 'text',
                'character_limit' => 0,
                'prepend' => null,
                'append' => null,
                'component' => 'text',
                'instructions' => null,
                'required' => false,
                'antlers' => false,
                'default' => null,
                'visibility' => 'visible',
                'read_only' => false, // deprecated
                'always_save' => false,
                'autocomplete' => null,
                'hide_display' => false,
                'instructions_position' => 'above',
                'listable' => 'hidden',
                'sortable' => true,
                'replicator_preview' => true,
                'duplicate' => true,
                'revisable' => true,
            ],
        ], $fields->toPublishArray());
    }

    #[Test]
    public function it_adds_values_to_fields()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', []);
        });

        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', []);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two'],
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $return = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertNotSame($fields->get('one'), $return->get('one'));
        $this->assertEquals(['one' => 'foo', 'two' => 'bar'], $return->values()->all());
    }

    #[Test]
    public function preprocessing_validatables_removes_unfilled_values()
    {
        $fields = new Fields([
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'one', 'field' => ['type' => 'text']],
            ['handle' => 'two', 'field' => ['type' => 'text']],
            ['handle' => 'reppy', 'field' => ['type' => 'replicator', 'sets' => ['replicator_set' => ['fields' => [
                ['handle' => 'title', 'field' => ['type' => 'text']],
                ['handle' => 'one', 'field' => ['type' => 'text']],
                ['handle' => 'two', 'field' => ['type' => 'text']],
                ['handle' => 'griddy_in_reppy', 'field' => ['type' => 'grid', 'fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text']],
                    ['handle' => 'one', 'field' => ['type' => 'text']],
                    ['handle' => 'two', 'field' => ['type' => 'text']],
                    ['handle' => 'bardo_in_griddy_in_reppy', 'field' => ['type' => 'bard', 'sets' => ['bard_set' => ['fields' => [
                        ['handle' => 'title', 'field' => ['type' => 'text']],
                        ['handle' => 'one', 'field' => ['type' => 'text']],
                        ['handle' => 'two', 'field' => ['type' => 'text']],
                        ['handle' => 'bardo_in_bardo_in_griddy_in_reppy', 'field' => ['type' => 'bard', 'sets' => ['bard_set_set' => ['fields' => [
                            ['handle' => 'title', 'field' => ['type' => 'text']],
                            ['handle' => 'one', 'field' => ['type' => 'text']],
                            ['handle' => 'two', 'field' => ['type' => 'text']],
                        ]]]]],
                    ]]]]],
                ]]],
            ]]]]],
        ]);

        $this->assertEquals(['title' => null, 'one' => null, 'two' => null, 'reppy' => null], $fields->values()->all());
        $this->assertEquals([], $fields->preProcessValidatables()->values()->all());

        $values = $expected = [
            'title' => 'recursion madness',
            'one' => 'foo',
            'reppy' => [
                ['type' => 'replicator_set', 'two' => 'foo'],
                ['type' => 'replicator_set', 'griddy_in_reppy' => [
                    ['one' => 'foo'],
                    ['bardo_in_griddy_in_reppy' => $bardValues = [
                        ['type' => 'set', 'attrs' => ['values' => ['type' => 'bard_set', 'two' => 'foo']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'foo']]],
                        ['type' => 'set', 'attrs' => ['type' => 'bard_set', 'values' => ['type' => 'bard_set', 'bardo_in_bardo_in_griddy_in_reppy' => $doubleNestedBardValues = [
                            ['type' => 'set', 'attrs' => ['values' => ['type' => 'bard_set', 'two' => 'foo']]],
                            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'foo']]],
                        ]]]],
                    ]],
                ]],
            ],
        ];

        // Calling `addValues()` should track which fields are filled at each level of nesting.
        // When we call `preProcessValidatables()`, unfilled values should get removed, in
        // order to ensure rules like `sometimes` and `required_if` work at all levels.
        $validatableValues = $fields->addValues($values)->preProcessValidatables()->values()->all();

        // Bard fields submit JSON values, so we'll replace them with their corresponding PHP array
        // values here, since `preProcessValidatables()` will return JSON decoded decoded values.
        $expected['reppy'][1]['griddy_in_reppy'][1]['bardo_in_griddy_in_reppy'] = $bardValues;
        $expected['reppy'][1]['griddy_in_reppy'][1]['bardo_in_griddy_in_reppy'][2]['attrs']['values']['bardo_in_bardo_in_griddy_in_reppy'] = $doubleNestedBardValues;

        $this->assertEquals($expected, $validatableValues);
    }

    #[Test]
    public function it_processes_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype
        {
            public function process($data)
            {
                return $data.' processed';
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
            ['handle' => 'two', 'field' => 'two'],
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $processed = $fields->process();

        $this->assertNotSame($fields, $processed);
        $this->assertEquals([
            'one' => 'foo',
            'two' => 'bar',
        ], $fields->values()->all());
        $this->assertEquals([
            'one' => 'foo processed',
            'two' => 'bar processed',
        ], $processed->values()->all());
    }

    #[Test]
    public function it_doesnt_return_computed_field_values()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'text']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'text', 'visibility' => 'computed', 'default' => 'gandalf']);
        });
        FieldRepository::shouldReceive('find')->with('three')->andReturnUsing(function () {
            return new Field('three', ['type' => 'text']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two'],
            ['handle' => 'three', 'field' => 'three'],
        ]);

        $this->assertEquals(['one' => null, 'three' => null], $fields->values()->all());
        $this->assertEquals(['one' => null, 'three' => null], $fields->process()->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals(['one' => 'foo', 'three' => 'baz'], $fields->values()->all());
        $this->assertEquals(['one' => 'foo', 'three' => 'baz'], $fields->process()->values()->all());
    }

    #[Test]
    public function it_does_return_computed_field_values_when_pre_processed()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'text']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'text', 'visibility' => 'computed', 'default' => 'gandalf']);
        });
        FieldRepository::shouldReceive('find')->with('three')->andReturnUsing(function () {
            return new Field('three', ['type' => 'text']);
        });

        $fields = new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two'],
            ['handle' => 'three', 'field' => 'three'],
        ]);

        $this->assertEquals(['one' => null, 'two' => 'gandalf', 'three' => null], $fields->preProcess()->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $this->assertEquals(['one' => 'foo', 'two' => 'bar', 'three' => 'baz'], $fields->preProcess()->values()->all());
    }

    #[Test]
    public function it_preprocesses_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype
        {
            public function preProcess($data)
            {
                return $data.' preprocessed';
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
            ['handle' => 'two', 'field' => 'two'],
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        $preProcessed = $fields->preProcess();

        $this->assertNotSame($fields, $preProcessed);
        $this->assertEquals([
            'one' => 'foo',
            'two' => 'bar',
        ], $fields->values()->all());
        $this->assertEquals([
            'one' => 'foo preprocessed',
            'two' => 'bar preprocessed',
        ], $preProcessed->values()->all());
    }

    #[Test]
    public function it_augments_each_fields_values_by_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype
        {
            public function augment($data)
            {
                return $data.' augmented';
            }

            public function shallowAugment($data)
            {
                return $data.' shallow augmented';
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
            ['handle' => 'two', 'field' => 'two'],
        ]);

        $this->assertEquals(['one' => null, 'two' => null], $fields->values()->all());

        $fields = $fields->addValues(['one' => 'foo', 'two' => 'bar', 'three' => 'baz']);

        tap($fields->augment(), function ($augmented) use ($fields) {
            $this->assertNotSame($fields, $augmented);
            $this->assertEquals([
                'one' => 'foo',
                'two' => 'bar',
            ], $fields->values()->all());
            $this->assertEquals([
                'one' => 'foo augmented',
                'two' => 'bar augmented',
            ], $augmented->values()->all());
        });

        tap($fields->shallowAugment(), function ($augmented) use ($fields) {
            $this->assertNotSame($fields, $augmented);
            $this->assertEquals([
                'one' => 'foo',
                'two' => 'bar',
            ], $fields->values()->all());
            $this->assertEquals([
                'one' => 'foo shallow augmented',
                'two' => 'bar shallow augmented',
            ], $augmented->values()->all());
        });
    }

    #[Test]
    public function it_gets_meta_data_from_all_fields()
    {
        FieldtypeRepository::shouldReceive('find')->with('fieldtype')->andReturn(new class extends Fieldtype
        {
            public function preload()
            {
                return 'meta data from field '.$this->field->handle().' is '.($this->field->value() * 2);
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
            ['handle' => 'two', 'field' => 'two'],
        ]))->addValues(['one' => 10, 'two' => 20]);

        $this->assertEquals([
            'one' => 'meta data from field one is 20',
            'two' => 'meta data from field two is 40',
        ], $fields->meta()->all());
    }

    #[Test]
    public function it_filters_down_to_localizable_fields()
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

    #[Test]
    public function it_gets_a_validator()
    {
        $fields = new Fields;
        Validator::shouldReceive('make')->once()->andReturnSelf();
        $mock = Validator::shouldReceive('fields')->once()->andReturnSelf()->getMock();

        $this->assertEquals($mock, $fields->validator());
    }

    #[Test]
    public function it_validates_immediately()
    {
        $fields = new Fields;
        Validator::shouldReceive('make')->once()->andReturnSelf();
        Validator::shouldReceive('fields')->once()->andReturnSelf();
        Validator::shouldReceive('withRules')->with([])->once()->andReturnSelf();
        Validator::shouldReceive('withMessages')->with([])->once()->andReturnSelf();
        Validator::shouldReceive('validate')->once();

        $fields->validate();
    }

    #[Test]
    public function it_validates_immediately_with_extra_rules()
    {
        $fields = new Fields;
        Validator::shouldReceive('make')->once()->andReturnSelf();
        Validator::shouldReceive('fields')->once()->andReturnSelf();
        Validator::shouldReceive('withRules')->with(['foo' => 'bar'])->once()->andReturnSelf();
        Validator::shouldReceive('withMessages')->with([])->once()->andReturnSelf();
        Validator::shouldReceive('validate')->once();

        $fields->validate(['foo' => 'bar']);
    }

    #[Test]
    public function it_validates_properly_against_filled_fields_with_sometimes_rule()
    {
        FieldRepository::shouldReceive('find')->with('one')->andReturnUsing(function () {
            return new Field('one', ['type' => 'text']);
        });
        FieldRepository::shouldReceive('find')->with('two')->andReturnUsing(function () {
            return new Field('two', ['type' => 'text']);
        });

        $fields = (new Fields([
            ['handle' => 'one', 'field' => 'one'],
            ['handle' => 'two', 'field' => 'two'],
        ]))->addValues(['one' => 'filled']);

        Validator::fields($fields)->withRules([])->validate();
        Validator::fields($fields)->withRules(['two' => ['sometimes', 'required']])->validate();

        try {
            Validator::fields($fields)->withRules(['two' => ['required']])->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            //
        }

        $this->assertInstanceOf(\Illuminate\Validation\ValidationException::class, $e);
    }

    #[Test]
    #[Group('graphql')]
    public function it_gets_the_fields_as_graphql_types()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['type' => 'text']],
            ['handle' => 'two', 'field' => ['type' => 'text', 'validate' => 'required']],
        ]);

        $types = $fields->toGql();

        $this->assertInstanceOf(Collection::class, $types);
        $this->assertCount(2, $types);

        $this->assertIsArray($types['one']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\NullableType::class, $types['one']['type']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\StringType::class, $types['one']['type']);

        $this->assertIsArray($types['two']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\NonNull::class, $types['two']['type']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\StringType::class, $types['two']['type']->getWrappedType());
    }

    #[Test]
    public function it_sets_the_parent_on_all_fields()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['type' => 'text']],
            ['handle' => 'two', 'field' => ['type' => 'text']],
        ]);

        $collection = $fields->all();
        $this->assertNull($collection['one']->parent());
        $this->assertNull($collection['two']->parent());

        $fields->setParent('foo');
        $collection = $fields->all();
        $this->assertEquals('foo', $collection['one']->parent());
        $this->assertEquals('foo', $collection['two']->parent());
    }

    #[Test]
    public function it_sets_the_parentfield_on_all_fields()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['type' => 'text']],
            ['handle' => 'two', 'field' => ['type' => 'text']],
        ]);

        $collection = $fields->all();
        $this->assertNull($collection['one']->parentField());
        $this->assertNull($collection['two']->parentField());

        $fields->setParentField('foo');
        $collection = $fields->all();
        $this->assertEquals('foo', $collection['one']->parentField());
        $this->assertEquals('foo', $collection['two']->parentField());
    }

    #[Test]
    public function it_sets_the_parentindex_on_all_fields()
    {
        $fields = new Fields([
            ['handle' => 'one', 'field' => ['type' => 'text']],
            ['handle' => 'two', 'field' => ['type' => 'text']],
        ]);

        $collection = $fields->all();
        $this->assertNull($collection['one']->parentIndex());
        $this->assertNull($collection['two']->parentIndex());

        $fields->setParentField('foo', 1);
        $collection = $fields->all();
        $this->assertEquals(1, $collection['one']->parentIndex());
        $this->assertEquals(1, $collection['two']->parentIndex());
    }

    #[Test]
    public function it_sets_the_parentfield_and_parentindex_on_imported_fields()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                ['handle' => 'bar', 'field' => ['type' => 'text']],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $parentField = new Field('foo', ['type' => 'replicator']);

        $fields = new Fields(
            [['import' => 'partial']],
            null,
            $parentField,
            1,
        );

        $collection = $fields->all();
        $this->assertEquals($parentField, $collection['bar']->parentField());
        $this->assertEquals(1, $collection['bar']->parentIndex());
    }

    #[Test]
    public function it_sets_the_parentfield_and_parentindex_on_referenced_fields()
    {
        $fieldset = (new Fieldset)->setHandle('partial')->setContents([
            'fields' => [
                ['handle' => 'bar', 'field' => ['type' => 'text']],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('partial')->once()->andReturn($fieldset);

        $parentField = new Field('foo', ['type' => 'replicator']);

        $fields = new Fields(
            [['handle' => 'bar', 'field' => 'partial.bar']],
            null,
            $parentField,
            1,
        );

        $collection = $fields->all();
        $this->assertEquals($parentField, $collection['bar']->parentField());
        $this->assertEquals(1, $collection['bar']->parentIndex());
    }

    #[Test]
    public function it_does_not_allow_recursive_imports()
    {
        $this->expectException(FieldsetRecursionException::class);

        $one = (new Fieldset)->setHandle('one')->setContents([
            'fields' => [
                [
                    'import' => 'two',
                ],
            ],
        ]);

        $two = (new Fieldset)->setHandle('two')->setContents([
            'fields' => [
                [
                    'import' => 'one',
                ],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('one')->zeroOrMoreTimes()->andReturn($one);
        FieldsetRepository::shouldReceive('find')->with('two')->zeroOrMoreTimes()->andReturn($two);

        new Fields([
            [
                'import' => 'one',
            ],
        ]);
    }

    #[Test]
    public function import_recursion_check_should_reset_across_instances()
    {
        $one = (new Fieldset)->setHandle('one')->setContents([
            'fields' => [
                [
                    'import' => 'two',
                ],
            ],
        ]);

        $two = (new Fieldset)->setHandle('two')->setContents([
            'fields' => [
                [
                    'handle' => 'foo',
                    'field' => ['type' => 'text'],
                ],
            ],
        ]);

        FieldsetRepository::shouldReceive('find')->with('one')->zeroOrMoreTimes()->andReturn($one);
        FieldsetRepository::shouldReceive('find')->with('two')->zeroOrMoreTimes()->andReturn($two);

        new Fields([
            [
                'import' => 'one',
            ],
        ]);

        new Fields([
            [
                'import' => 'two',
            ],
        ]);
    }
}
