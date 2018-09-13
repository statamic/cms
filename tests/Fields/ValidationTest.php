<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Validation;
use Facades\Tests\FakeFieldsetLoader;
use Facades\Tests\Factories\FieldsetFactory;

/** @group fields */
class ValidationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->app['statamic.fieldtypes']['fieldtype_with_rules'] = FieldtypeWithValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_no_rules'] = FieldtypeWithNoValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_extra_rules'] = FieldtypeWithExtraValidationRules::class;
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

        $return = $validation->with([
            'foo' => 'required',
            'test' => 'required|array'
        ]);

        $this->assertEquals($validation, $return);
        $this->assertEquals([
            'foo' => ['required'],
            'test' => ['min:10', 'required', 'array', ]
        ], $validation->rules());
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
        ], (new Validation)->fieldset($fieldset)->rules());
    }

    private function compile($fields)
    {
        $fieldset = $this->createFieldsetWith($fields);

        return (new Validation)->fieldset($fieldset);
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
