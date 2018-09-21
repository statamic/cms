<?php

namespace Tests\Fields;

use Mockery;
use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Extend\Fieldtype;
use Statamic\Fields\Validation;
use Illuminate\Support\Collection;
use Facades\Statamic\Fields\FieldtypeRepository;

class ValidationTest extends TestCase
{
    /** @test */
    function it_explodes_pipe_style_rules_into_arrays()
    {
        $this->assertEquals(['foo'], Validation::explodeRules('foo'));

        $this->assertEquals(['foo', 'bar'], Validation::explodeRules('foo|bar'));

        $this->assertEquals([], Validation::explodeRules(null));

        $this->assertEquals(['foo', 'bar'], Validation::explodeRules(['foo', 'bar']));
    }

    /** @test */
    function it_merges_rules()
    {
        $original = [
            'one' => ['required'],
            'two' => ['array'],
        ];

        $overrides = [
            'one' => ['min:20'],
            'three' => ['required'],
        ];

        $merged = (new Validation)->merge($original, $overrides);

        $this->assertInstanceOf(Collection::class, $merged);
        $this->assertEquals([
            'one' => ['required', 'min:20'],
            'two' => ['array'],
            'three' => ['required'],
        ], $merged->all());
    }

    /** @test */
    function it_compiles_field_rules()
    {
        $fieldWithItsOwnRules = Mockery::mock(Field::class);
        $fieldWithItsOwnRules->shouldReceive('rules')->andReturn(['one' => ['required']]);

        $fieldWithExtraRules = Mockery::mock(Field::class);
        $fieldWithExtraRules->shouldReceive('rules')->andReturn([
            'two' => ['required', 'array'],
            'another' => ['min:2'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([
            $fieldWithItsOwnRules,
            $fieldWithExtraRules,
        ]));

        $validation = (new Validation)->fields($fields);

        $this->assertEquals([
            'one' => ['required'],
            'two' => ['required', 'array'],
            'another' => ['min:2'],
        ], $validation->rules());
    }

    /** @test */
    function it_adds_additional_rules()
    {
        $validation = (new Validation)->withRules([
            'foo' => 'required',
            'test' => 'required|array'
        ]);

        $this->assertEquals([
            'foo' => ['required'],
            'test' => ['required', 'array'],
        ], $validation->rules());
    }

    /** @test */
    function it_merges_additional_rules_into_field_rules()
    {
        $field = Mockery::mock(Field::class);
        $field->shouldReceive('rules')->andReturn([
            'one' => ['required', 'array'],
            'extra' => ['min:2'],
        ]);

        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('all')->andReturn(collect([$field]));

        $validation = (new Validation)->fields($fields)->withRules([
            'one' => 'required|min:2',
            'additional' => 'required'
        ]);

        $this->assertEquals([
            'one' => ['required', 'array', 'min:2'], // notice required is deduplicated.
            'extra' => ['min:2'],
            'additional' => ['required']
        ], $validation->rules());
    }
}
