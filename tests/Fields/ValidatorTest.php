<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Validator;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    #[Test]
    public function it_explodes_pipe_style_rules_into_arrays()
    {
        $this->assertEquals(['foo'], Validator::explodeRules('foo'));

        $this->assertEquals(['foo', 'bar'], Validator::explodeRules('foo|bar'));

        $this->assertEquals([], Validator::explodeRules(null));

        $this->assertEquals(['foo', 'bar'], Validator::explodeRules(['foo', 'bar']));
    }

    #[Test]
    public function it_merges_rules()
    {
        $original = [
            'one' => ['required'],
            'two' => ['array'],
        ];

        $overrides = [
            'one' => ['min:20'],
            'three' => ['required'],
        ];

        $merged = (new Validator)->merge($original, $overrides);

        $this->assertInstanceOf(Collection::class, $merged);
        $this->assertEquals([
            'one' => ['required', 'min:20'],
            'two' => ['array'],
            'three' => ['required'],
        ], $merged->all());
    }

    #[Test]
    public function it_compiles_field_rules()
    {
        $fieldWithItsOwnRules = Mockery::mock(Field::class);
        $fieldWithItsOwnRules->shouldReceive('setValidationContext')->with([])->andReturnSelf();
        $fieldWithItsOwnRules->shouldReceive('rules')->andReturn(['one' => ['required']]);

        $fieldWithExtraRules = Mockery::mock(Field::class);
        $fieldWithExtraRules->shouldReceive('setValidationContext')->with([])->andReturnSelf();
        $fieldWithExtraRules->shouldReceive('rules')->andReturn([
            'two' => ['required', 'array'],
            'another' => ['min:2'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();
        $fields->shouldReceive('all')->andReturn(collect([
            $fieldWithItsOwnRules,
            $fieldWithExtraRules,
        ]));

        $validation = (new Validator)->fields($fields);

        $this->assertEquals([
            'one' => ['required'],
            'two' => ['required', 'array'],
            'another' => ['min:2'],
        ], $validation->rules());
    }

    #[Test]
    public function it_adds_additional_rules()
    {
        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([]));
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();

        $validation = (new Validator)->fields($fields)->withRules([
            'foo' => 'required',
            'test' => 'required|array',
        ]);

        $this->assertEquals([
            'foo' => ['required'],
            'test' => ['required', 'array'],
        ], $validation->rules());
    }

    #[Test]
    public function it_merges_additional_rules_into_field_rules()
    {
        $field = Mockery::mock(Field::class);
        $field->shouldReceive('setValidationContext')->with([])->andReturnSelf();
        $field->shouldReceive('rules')->andReturn([
            'one' => ['required', 'array'],
            'extra' => ['min:2'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([$field]));
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();

        $validation = (new Validator)->fields($fields)->withRules([
            'one' => 'required|min:2',
            'additional' => 'required',
        ]);

        $this->assertEquals([
            'one' => ['required', 'array', 'min:2'], // notice required is deduplicated.
            'extra' => ['min:2'],
            'additional' => ['required'],
        ], $validation->rules());
    }

    #[Test]
    public function it_compiles_field_attributes()
    {
        $fieldWithNoExtraAttributes = Mockery::mock(Field::class);
        $fieldWithNoExtraAttributes->shouldReceive('validationAttributes')->andReturn(['one' => 'One']);

        $fieldWithExtraAttributes = Mockery::mock(Field::class);
        $fieldWithExtraAttributes->shouldReceive('validationAttributes')->andReturn([
            'two' => 'Two',
            'extra_one' => 'Extra One',
            'extra_two' => 'Extra Two',
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([
            $fieldWithNoExtraAttributes,
            $fieldWithExtraAttributes,
        ]));
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();

        $validation = (new Validator)->fields($fields);

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'extra_one' => 'Extra One',
            'extra_two' => 'Extra Two',
        ], $validation->attributes());
    }

    #[Test]
    public function it_makes_string_based_replacements()
    {
        $field = Mockery::mock(Field::class);
        $field->shouldReceive('setValidationContext')->with([])->andReturnSelf();
        $field->shouldReceive('rules')->andReturn([
            'one' => ['required', 'test:{foo}'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([$field]));
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();

        $validation = (new Validator)->fields($fields)->withRules([
            'one' => 'test:{bar}',
            'two' => 'another:{baz},{qux},{quux}',
        ])->withReplacements([
            'foo' => 'FOO',
            'bar' => 'BAR',
            'baz' => 'BAZ',
            'quux' => 'QUUX',
        ]);

        $this->assertEquals([
            'one' => ['required', 'test:FOO', 'test:BAR'],
            'two' => ['another:BAZ,NULL,QUUX'],
        ], $validation->rules());
    }

    #[Test]
    public function it_makes_class_based_replacements()
    {
        $field = Mockery::mock(Field::class);
        $field->shouldReceive('setValidationContext')->with([])->andReturnSelf();
        $field->shouldReceive('rules')->andReturn([
            'one' => ['new \\Tests\\Fields\\FakeRule({string}, {zero}, {num}, {true}, {false}, {null})'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([$field]));
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();

        $validation = (new Validator)->fields($fields)->withReplacements([
            'string' => 'FOO',
            'zero' => 0,
            'num' => 7,
            'true' => true,
            'false' => false,
            'null' => null,
        ]);

        $rule = $validation->rules()['one'][0];

        $this->assertSame('FOO', $rule->string);
        $this->assertSame(0, $rule->zero);
        $this->assertSame(7, $rule->num);
        $this->assertSame(true, $rule->true);
        $this->assertSame(false, $rule->false);
        $this->assertSame(null, $rule->null);
    }

    #[Test]
    public function it_does_not_make_replacements_in_regex_rules()
    {
        $field = Mockery::mock(Field::class);
        $field->shouldReceive('setValidationContext')->with([])->andReturnSelf();
        $field->shouldReceive('rules')->andReturn([
            'one' => ['required', 'test:{foo}', 'regex:/^\d{1}$/', 'not_regex:/^\d{2}$/'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([$field]));
        $fields->shouldReceive('preProcessValidatables')->andReturnSelf();

        $validation = (new Validator)->fields($fields)->withRules([
            'one' => 'not_regex:/^\d{3}$/',
            'two' => 'regex:/^\d{2}$/',
        ])->withReplacements([
            'foo' => 'FOO',
            '1' => 'ONE',
            '2' => 'TWO',
            '3' => 'THREE',
        ]);

        $this->assertEquals([
            'one' => ['required', 'test:FOO', 'regex:/^\d{1}$/', 'not_regex:/^\d{2}$/', 'not_regex:/^\d{3}$/'],
            'two' => ['regex:/^\d{2}$/'],
        ], $validation->rules());
    }

    #[Test]
    public function it_replaces_this_in_sets()
    {
        $replicator = [
            'type' => 'replicator',
            'sets' => [
                'replicator_set' => [
                    'fields' => [
                        ['handle' => 'must_fill', 'field' => ['type' => 'toggle']],
                        ['handle' => 'text', 'field' => ['validate' => ['required_if:{this}.must_fill,true']]],
                    ],
                ],
            ],
        ];

        $grid = [
            'type' => 'grid',
            'fields' => [
                ['handle' => 'must_fill', 'field' => ['type' => 'toggle']],
                ['handle' => 'text', 'field' => ['validate' => ['required_if:{this}.must_fill,true']]],
            ],
        ];

        $bard = [
            'type' => 'bard',
            'sets' => [
                'bard_set' => [
                    'fields' => [
                        ['handle' => 'must_fill', 'field' => ['type' => 'toggle']],
                        ['handle' => 'text', 'field' => ['validate' => ['required_if:{this}.must_fill,true']]],
                    ],
                ],
            ],
        ];

        $replicatorWithNestedReplicator = [
            'type' => 'replicator',
            'sets' => [
                'replicator_set' => [
                    'fields' => [
                        ['handle' => 'nested_replicator', 'field' => $replicator],
                    ],
                ],
            ],
        ];

        $replicatorWithDoubleNestedReplicator = [
            'type' => 'replicator',
            'sets' => [
                'replicator_set' => [
                    'fields' => [
                        ['handle' => 'nested_replicator', 'field' => $replicatorWithNestedReplicator],
                    ],
                ],
            ],
        ];

        $replicatorWithNestedGrid = [
            'type' => 'replicator',
            'sets' => [
                'replicator_set' => [
                    'fields' => [
                        ['handle' => 'nested_grid', 'field' => $grid],
                    ],
                ],
            ],
        ];

        $replicatorWithNestedBard = [
            'type' => 'replicator',
            'sets' => [
                'replicator_set' => [
                    'fields' => [
                        ['handle' => 'nested_bard', 'field' => $bard],
                    ],
                ],
            ],
        ];

        $gridWithNestedReplicator = [
            'type' => 'grid',
            'fields' => [
                ['handle' => 'nested_replicator', 'field' => $replicator],
            ],
        ];

        $bardWithNestedReplicator = [
            'type' => 'bard',
            'sets' => [
                'bard_set' => [
                    'fields' => [
                        ['handle' => 'nested_replicator', 'field' => $replicator],
                    ],
                ],
            ],
        ];

        $fields = new Fields([
            ['handle' => 'replicator', 'field' => $replicator],
            ['handle' => 'replicator_with_nested_replicator', 'field' => $replicatorWithNestedReplicator],
            ['handle' => 'replicator_with_double_nested_replicator', 'field' => $replicatorWithDoubleNestedReplicator],
            ['handle' => 'replicator_with_nested_grid', 'field' => $replicatorWithNestedGrid],
            ['handle' => 'replicator_with_nested_bard', 'field' => $replicatorWithNestedBard],
            ['handle' => 'grid', 'field' => $grid],
            ['handle' => 'grid_with_nested_replicator', 'field' => $gridWithNestedReplicator],
            ['handle' => 'bard', 'field' => $bard],
            ['handle' => 'bard_with_nested_replicator', 'field' => $bardWithNestedReplicator],
        ]);

        $fields = $fields->addValues([
            'replicator' => [
                ['type' => 'replicator_set'],
            ],
            'replicator_with_nested_replicator' => [
                ['type' => 'replicator_set', 'nested_replicator' => [['type' => 'replicator_set']]],
            ],
            'replicator_with_double_nested_replicator' => [
                ['type' => 'replicator_set', 'nested_replicator' => [['type' => 'replicator_set', 'nested_replicator' => [['type' => 'replicator_set']]]]],
            ],
            'replicator_with_nested_grid' => [
                ['type' => 'replicator_set', 'nested_grid' => [
                    ['text' => null, 'not_in_blueprint' => 'test'],
                ]],
            ],
            'replicator_with_nested_bard' => [
                ['type' => 'replicator_set', 'nested_bard' => [['type' => 'set', 'attrs' => ['values' => ['type' => 'bard_set']]]]],
            ],
            'grid' => [
                ['text' => null, 'not_in_blueprint' => 'test'],
            ],
            'grid_with_nested_replicator' => [
                ['nested_replicator' => [['type' => 'replicator_set']]],
            ],
            'bard' => [
                ['type' => 'set', 'attrs' => ['values' => ['type' => 'bard_set']]],
            ],
            'bard_with_nested_replicator' => [
                ['type' => 'set', 'attrs' => ['values' => ['type' => 'bard_set', 'nested_replicator' => [['type' => 'replicator_set']]]]],
            ],
        ]);

        $validator = (new Validator)->fields($fields);
        $rules = $validator->rules();

        $this->assertArraySubset([
            'replicator.0.text' => [
                'required_if:replicator.0.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'replicator_with_nested_replicator.0.nested_replicator.0.text' => [
                'required_if:replicator_with_nested_replicator.0.nested_replicator.0.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'replicator_with_double_nested_replicator.0.nested_replicator.0.nested_replicator.0.text' => [
                'required_if:replicator_with_double_nested_replicator.0.nested_replicator.0.nested_replicator.0.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'replicator_with_nested_grid.0.nested_grid.0.text' => [
                'required_if:replicator_with_nested_grid.0.nested_grid.0.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'replicator_with_nested_bard.0.nested_bard.0.attrs.values.text' => [
                'required_if:replicator_with_nested_bard.0.nested_bard.0.attrs.values.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'grid.0.text' => [
                'required_if:grid.0.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'grid_with_nested_replicator.0.nested_replicator.0.text' => [
                'required_if:grid_with_nested_replicator.0.nested_replicator.0.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'bard.0.attrs.values.text' => [
                'required_if:bard.0.attrs.values.must_fill,true',
            ],
        ], $rules);

        $this->assertArraySubset([
            'bard_with_nested_replicator.0.attrs.values.nested_replicator.0.text' => [
                'required_if:bard_with_nested_replicator.0.attrs.values.nested_replicator.0.must_fill,true',
            ],
        ], $rules);

        $this->assertEquals([
            'replicator' => 'Replicator',
            'replicator.0.must_fill' => 'Must Fill',
            'replicator.0.text' => 'Text',
            'replicator_with_nested_replicator' => 'Replicator With Nested Replicator',
            'replicator_with_nested_replicator.0.nested_replicator' => 'Nested Replicator',
            'replicator_with_nested_replicator.0.nested_replicator.0.must_fill' => 'Must Fill',
            'replicator_with_nested_replicator.0.nested_replicator.0.text' => 'Text',
            'replicator_with_double_nested_replicator' => 'Replicator With Double Nested Replicator',
            'replicator_with_double_nested_replicator.0.nested_replicator' => 'Nested Replicator',
            'replicator_with_double_nested_replicator.0.nested_replicator.0.nested_replicator' => 'Nested Replicator',
            'replicator_with_double_nested_replicator.0.nested_replicator.0.nested_replicator.0.must_fill' => 'Must Fill',
            'replicator_with_double_nested_replicator.0.nested_replicator.0.nested_replicator.0.text' => 'Text',
            'replicator_with_nested_grid' => 'Replicator With Nested Grid',
            'replicator_with_nested_grid.0.nested_grid' => 'Nested Grid',
            'replicator_with_nested_grid.0.nested_grid.0.must_fill' => 'Must Fill',
            'replicator_with_nested_grid.0.nested_grid.0.text' => 'Text',
            'replicator_with_nested_bard' => 'Replicator With Nested Bard',
            'replicator_with_nested_bard.0.nested_bard' => 'Nested Bard',
            'replicator_with_nested_bard.0.nested_bard.0.attrs.values.must_fill' => 'Must Fill',
            'replicator_with_nested_bard.0.nested_bard.0.attrs.values.text' => 'Text',
            'grid' => 'Grid',
            'grid.0.text' => 'Text',
            'grid.0.must_fill' => 'Must Fill',
            'grid_with_nested_replicator' => 'Grid With Nested Replicator',
            'grid_with_nested_replicator.0.nested_replicator' => 'Nested Replicator',
            'bard' => 'Bard',
            'bard.0.attrs.values.must_fill' => 'Must Fill',
            'bard.0.attrs.values.text' => 'Text',
            'bard_with_nested_replicator' => 'Bard With Nested Replicator',
            'bard_with_nested_replicator.0.attrs.values.nested_replicator' => 'Nested Replicator',
            'bard_with_nested_replicator.0.attrs.values.nested_replicator.0.must_fill' => 'Must Fill',
            'bard_with_nested_replicator.0.attrs.values.nested_replicator.0.text' => 'Text',
        ], $validator->attributes());
    }

    #[Test]
    public function it_discards_this_at_top_level()
    {
        $fields = new Fields([
            ['handle' => 'must_fill', 'field' => ['type' => 'toggle']],
            ['handle' => 'text', 'field' => ['validate' => ['required_if:{this}.must_fill,true']]],
        ]);

        $rules = (new Validator)->fields($fields)->rules();

        $this->assertArraySubset([
            'text' => [
                'required_if:must_fill,true',
            ],
        ], $rules);
    }
}

class FakeRule
{
    public function __construct(
        public $string,
        public $zero,
        public $num,
        public $true,
        public $false,
        public $null
    ) {
        //
    }
}
