<?php

namespace Tests\Fields;

use Illuminate\Support\Collection;
use Mockery;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Validator;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    /** @test */
    public function it_explodes_pipe_style_rules_into_arrays()
    {
        $this->assertEquals(['foo'], Validator::explodeRules('foo'));

        $this->assertEquals(['foo', 'bar'], Validator::explodeRules('foo|bar'));

        $this->assertEquals([], Validator::explodeRules(null));

        $this->assertEquals(['foo', 'bar'], Validator::explodeRules(['foo', 'bar']));
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

     /** @test */
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

    /** @test */
    public function it_makes_replacements()
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

    /** @test */
    public function it_replaces_this()
    {
        $replicator = [
            'type' => 'replicator',
            'sets' => [
                'replicator_set' => [
                    'fields' => [
                        ['handle' => 'must_fill', 'field' => ['type' => 'toggle']],
                        [
                            'handle' => 'text',
                            'field' => [
                                'validate' => ['required_if:{this}.must_fill,true'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $grid = [
            'type' => 'grid',
            'fields' => [
                ['handle' => 'must_fill', 'field' => ['type' => 'toggle']],
                [
                    'handle' => 'text',
                    'field' => [
                        'validate' => ['required_if:{this}.must_fill,true'],
                    ],
                ],
            ],
        ];

        $replicatorWithNestedReplicator = [
            'type' => 'replicator',
            'sets' => [
                'parent_replicator_set' => [
                    'fields' => [
                        ['handle' => 'nested_replicator', 'field' => $replicator],
                    ],
                ],
            ],
        ];

        $replicatorWithNestedGrid = [
            'type' => 'replicator',
            'sets' => [
                'parent_replicator_set' => [
                    'fields' => [
                        ['handle' => 'nested_grid', 'field' => $grid],
                    ],
                ],
            ],
        ];

        $gridWithNestedReplicator = [
            'type' => 'grid',
            'fields' => ['handle' => 'nested_replicator', 'field' => $replicator],
        ];

        $fields = new Fields([
            ['handle' => 'replicator', 'field' => $replicator],
            ['handle' => 'replicator_with_nested_replicator', 'field' => $replicatorWithNestedReplicator],
            ['handle' => 'replicator_with_nested_grid', 'field' => $replicatorWithNestedGrid],
            ['handle' => 'grid', 'field' => $grid],
            ['handle' => 'grid_with_nested_replicator', 'field' => $gridWithNestedReplicator],
        ]);
        
        $fields = $fields->addValues([
            'replicator' => [
                ['type' => 'replicator_set'],
            ],
            'replicator_with_nested_replicator' => [
                ['type' => 'parent_replicator_set', 'nested_replicator' => [['type' => 'replicator_set']]],
            ],
            'replicator_with_nested_grid' => [
                ['type' => 'parent_replicator_set', 'nested_grid' => [['text' => 'null']]],
            ],
            'grid' => [
                ['text' => null],
            ],
            'grid_with_nested_replicator' => [
                // TODO 
            ],
        ]);

        $validation = (new Validator)->fields($fields);

        // print_r($validation->rules());

        $this->assertArraySubset([
            'replicator.0.text' => [
                'required_if:replicator.0.must_fill,true',
            ],
            'grid.0.text' => [
                'required_if:grid.0.must_fill,true',
            ],
            'replicator_with_nested_replicator.0.nested_replicator.0.text' => [
                'required_if:replicator_with_nested_replicator.0.nested_replicator.0.must_fill,true',
            ],
            'replicator_with_nested_grid.0.nested_grid.0.text' => [
                'required_if:replicator_with_nested_grid.0.nested_grid.0.must_fill,true',
            ],
        ], $validation->rules());
    }
}
