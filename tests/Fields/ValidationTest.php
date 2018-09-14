<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Extend\Fieldtype;
use Statamic\Fields\Validation;
use Facades\Statamic\Fields\FieldtypeRepository;

class ValidationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($this->fieldtypeWithRules());

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($this->fieldtypeWithNoRules());

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_extra_rules')
            ->andReturn($this->fieldtypeWithExtraRules());
    }

    /** @test */
    function it_explodes_pipe_style_rules_into_arrays()
    {
        $this->assertEquals(['foo'], Validation::explodeRules('foo'));

        $this->assertEquals(['foo', 'bar'], Validation::explodeRules('foo|bar'));

        $this->assertEquals([], Validation::explodeRules(null));

        $this->assertEquals(['foo', 'bar'], Validation::explodeRules(['foo', 'bar']));
    }

    /** @test */
    function it_compiles_rules()
    {
        $validation = $this->compile([
            'fieldtype_and_field_rules' => [
                'type' => 'fieldtype_with_rules',
                'validate' => 'required|array'
            ],
            'fieldtype_rules_only' => [
                'type' => 'fieldtype_with_rules',
            ],
            'field_rules_only' => [
                'type' => 'fieldtype_with_no_rules',
                'validate' => 'required|min:10'
            ],
            'extra_fieldtype_rules' => [
                'type' => 'fieldtype_with_extra_rules',
            ],
            'no_rules' => [
                'type' => 'fieldtype_with_no_rules'
            ]
        ]);

        $this->assertEquals([
            'fieldtype_and_field_rules' => ['required', 'array', 'min:2', 'max:5'],
            'fieldtype_rules_only' => ['min:2', 'max:5'],
            'field_rules_only' => ['required', 'min:10'],
            'extra_fieldtype_rules' => [],
            'test.*.one' => ['required', 'min:2'],
            'test.*.two' => ['max:2'],
            'no_rules' => [],
        ], $validation->rules());
    }

    /** @test */
    function it_adds_additional_rules()
    {
        $validation = $this->compile([
            'test' => [
                'type' => 'fieldtype_with_no_rules',
                'validate' => 'min:10'
            ]
        ]);

        $return = $validation->withRules([
            'foo' => 'required',
            'test' => 'required|array'
        ]);

        $this->assertEquals($validation, $return);
        $this->assertEquals([
            'foo' => ['required'],
            'test' => ['min:10', 'required', 'array', ]
        ], $validation->rules());
    }

    private function compile($fields)
    {
        $fields = collect($fields)->map(function ($config, $handle) {
            return new Field($handle, $config);
        });

        return (new Validation)->fields($fields);
    }

    private function fieldtypeWithNoRules()
    {
        return new class extends Fieldtype
        {
            public function rules()
            {
                return null;
            }
        };
    }

    private function fieldtypeWithRules()
    {
        return new class extends Fieldtype
        {
            public function rules()
            {
                return 'min:2|max:5';
            }
        };
    }

    private function fieldtypeWithExtraRules()
    {
        return new class extends Fieldtype
        {
            public function extraRules($data)
            {
                return [
                    'test.*.one' => 'required|min:2',
                    'test.*.two' => 'max:2'
                ];
            }
        };
    }
}
