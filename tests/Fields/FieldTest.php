<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Tests\Fakes\Fieldtypes\FieldtypeWithValidationRules;
use Tests\Fakes\Fieldtypes\FieldtypeWithNoValidationRules;
use Tests\Fakes\Fieldtypes\FieldtypeWithExtraValidationRules;

/** @group fields */
class FieldTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->app['statamic.fieldtypes']['fieldtype_with_rules'] = FieldtypeWithValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_no_rules'] = FieldtypeWithNoValidationRules::class;
        $this->app['statamic.fieldtypes']['fieldtype_with_extra_rules'] = FieldtypeWithExtraValidationRules::class;
    }

    /** @test */
    function it_gets_validation_rules_from_field()
    {
        $field = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required|min:2'
        ]);

        $this->assertEquals([
            'test' => ['required', 'min:2']
        ], $field->rules());
    }

    /** @test */
    function it_gets_validation_rules_from_fieldtype()
    {
        $field = new Field('test', [
            'type' => 'fieldtype_with_rules',
        ]);

        $this->assertEquals([
            'test' => ['min:2', 'max:5']
        ], $field->rules());
    }

    /** @test */
    function it_merges_validation_rules_from_field_with_fieldtype()
    {
        $field = new Field('test', [
            'type' => 'fieldtype_with_rules',
            'validate' => 'required|array'
        ]);

        $this->assertEquals([
            'test' => ['required', 'array', 'min:2', 'max:5']
        ], $field->rules());
    }

    /** @test */
    function it_merges_extra_fieldtype_rules()
    {
        $field = new Field('test', [
            'type' => 'fieldtype_with_extra_rules',
            'validate' => 'required'
        ]);

        $this->assertEquals([
            'test' => ['required'],
            'test.*.one' => ['required', 'min:2'],
            'test.*.two' => ['max:2'],
        ], $field->rules());
    }

    /** @test */
    function it_gets_the_fieldtype()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_gets_the_display_value()
    {
        $this->assertEquals(
            'Test Display Value',
            (new Field('test', ['display' => 'Test Display Value']))->display()
        );

        $this->assertEquals(
            'Test',
            (new Field('test', []))->display()
        );

        $this->assertEquals(
            'Test multi word handle and no explicit display',
            (new Field('test_multi_word_handle_and_no_explicit_display', []))->display()
        );
    }
}
