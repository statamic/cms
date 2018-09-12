<?php

namespace Tests\Validation;

use Tests\TestCase;
use Statamic\CP\Fieldset;
use Statamic\Validation\Compiler;
use Facades\Tests\FakeFieldsetLoader;
use Facades\Tests\Factories\FieldsetFactory;

/** @group fields */
class CompilerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->app['statamic.fieldtypes']['fieldtype_with_rules'] = FieldtypeWithValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_no_rules'] = FieldtypeWithNoValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_extra_rules'] = FieldtypeWithExtraValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_extra_attributes'] = FieldtypeWithExtraAttributes::class;
    }

    /** @test */
    function it_explodes_pipe_style_rules_into_arrays()
    {
        $this->assertEquals(['foo'], Compiler::explodeRules('foo'));

        $this->assertEquals(['foo', 'bar'], Compiler::explodeRules('foo|bar'));

        $this->assertEquals([], Compiler::explodeRules(null));

        $this->assertEquals(['foo', 'bar'], Compiler::explodeRules(['foo', 'bar']));
    }

    /** @test */
    function it_compiles_rules()
    {
        $compiler = $this->compile([
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
        ], $compiler->rules());
    }

    /** @test */
    function it_adds_additional_rules()
    {
        $compiler = $this->compile([
            'test' => [
                'type' => 'fieldtype_with_no_rules',
                'validate' => 'min:10'
            ]
        ]);

        $return = $compiler->with([
            'foo' => 'required',
            'test' => 'required|array'
        ]);

        $this->assertEquals($compiler, $return);
        $this->assertEquals([
            'foo' => ['required'],
            'test' => ['min:10', 'required', 'array', ]
        ], $compiler->rules());
    }

    /** @test */
    function it_inlines_rules_from_partials()
    {
        FakeFieldsetLoader::bind()->with('the_partial', function ($fieldset) {
            return $fieldset->withFields([
                'fieldtype_and_field_rules' => [
                    'type' => 'fieldtype_with_rules',
                    'validate' => 'required|array'
                ],
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'partial' => ['type' => 'partial', 'fieldset' => 'the_partial']
        ])->create();

        $this->assertEquals([
            'fieldtype_and_field_rules' => ['required', 'array', 'min:2', 'max:5']
        ], (new Compiler)->fieldset($fieldset)->rules());
    }

    /** @test */
    function it_compiles_attributes()
    {
        $compiler = $this->compile([
            'field_with_display' => [
                'display' => 'Field One'
            ],
            'field_with_no_display' => [],
            'field_with_extra_attributes' => [
                'display' => 'Extras',
                'type' => 'fieldtype_with_extra_attributes'
            ]
        ]);

        $this->assertEquals([
            'field_with_display' => 'Field One',
            'field_with_no_display' => 'Field with no display',
            'field_with_extra_attributes' => 'Extras',
            'extra.*.one' => 'Extra One',
            'extra.*.two' => 'Extra Two',
        ], $compiler->attributes());
    }

    /** @test */
    function it_inlines_attributes_from_partials()
    {
        FakeFieldsetLoader::bind()->with('the_partial', function ($fieldset) {
            return $fieldset->withFields([
                'nested_field' => ['display' => 'Nested Field'],
                'nested_field_with_no_explicit_display' => []
            ]);
        });

        $fieldset = FieldsetFactory::withFields([
            'partial' => ['type' => 'partial', 'fieldset' => 'the_partial']
        ])->create();

        $this->assertEquals([
            'nested_field' => 'Nested Field',
            'nested_field_with_no_explicit_display' => 'Nested field with no explicit display'
        ], (new Compiler)->fieldset($fieldset)->attributes());
    }

    private function compile($fields)
    {
        $fieldset = $this->createFieldsetWith($fields);

        return (new Compiler)->fieldset($fieldset);
    }

    private function createFieldsetWith($fields)
    {
        $fieldset = new Fieldset;
        $fieldset->contents(['fields' => $fields]);
        return $fieldset;
    }
}

class FieldtypeWithNoValidationRules extends \Statamic\Extend\Fieldtype
{
    //
}

class FieldtypeWithValidationRules extends \Statamic\Extend\Fieldtype
{
    public function rules()
    {
        return 'min:2|max:5';
    }
}

class FieldtypeWithExtraValidationRules extends \Statamic\Extend\Fieldtype
{
    public function extraRules()
    {
        return [
            'test.*.one' => 'required|min:2',
            'test.*.two' => 'max:2'
        ];
    }
}

class FieldtypeWithExtraAttributes extends \Statamic\Extend\Fieldtype
{
    public function extraAttributes()
    {
        return [
            'extra.*.one' => 'Extra One',
            'extra.*.two' => 'Extra Two'
        ];
    }
}
