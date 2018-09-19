<?php

namespace Tests\Fields;

use Tests\TestCase;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Addons\Text\TextFieldtype;

class FieldtypeTest extends TestCase
{
    /** @test */
    function it_gets_the_field()
    {
        $fieldtype = new TestFieldtype;
        $field = new Field('test', ['foo' => 'bar']);

        $this->assertNull($fieldtype->field());

        $return = $fieldtype->setField($field);

        $this->assertEquals($fieldtype, $return);
        $this->assertEquals($field, $fieldtype->field());
    }

    /** @test */
    function the_handle_is_snake_cased_from_the_class_by_default()
    {
        $this->assertEquals(
            'test_multi_word',
            (new TestMultiWordFieldtype)->handle()
        );

        $this->assertEquals(
            'test_multi_word_with_no_fieldtype_suffix',
            (new TestMultiWordWithNoFieldtypeSuffix)->handle()
        );
    }

    /** @test */
    function handle_can_be_defined_as_a_property()
    {
        $fieldtype = new class extends Fieldtype {
            protected $handle = 'example';
        };

        $this->assertEquals('example', $fieldtype->handle());
    }

    /** @test */
    function title_is_the_humanized_handle_by_default()
    {
        $this->assertEquals(
            'Test multi word',
            (new TestMultiWordFieldtype)->title()
        );

        $this->assertEquals(
            'Test multi word with no fieldtype suffix',
            (new TestMultiWordWithNoFieldtypeSuffix)->title()
        );
    }

    /** @test */
    function title_can_be_defined_as_a_property()
    {
        $fieldtype = new class extends Fieldtype {
            protected $title = 'Super Cool Example';
        };

        $this->assertEquals('Super Cool Example', $fieldtype->title());
    }

    /** @test */
    function localization_can_be_disabled()
    {
        $this->assertTrue((new TestFieldtype)->localizable());

        $fieldtype = new class extends Fieldtype {
            protected $localizable = false;
        };

        $this->assertFalse($fieldtype->localizable());
    }

    /** @test */
    function validation_can_be_disabled()
    {
        $this->assertTrue((new TestFieldtype)->validatable());

        $fieldtype = new class extends Fieldtype {
            protected $validatable = false;
        };

        $this->assertFalse($fieldtype->validatable());
    }

    /** @test */
    function default_values_can_be_disabled()
    {
        $this->assertTrue((new TestFieldtype)->defaultable());

        $fieldtype = new class extends Fieldtype {
            protected $defaultable = false;
        };

        $this->assertFalse($fieldtype->defaultable());
    }

    /** @test */
    function it_belongs_to_the_text_category_by_default()
    {
        $this->assertEquals(['text'], (new TestFieldtype)->categories());

        $fieldtype = new class extends Fieldtype {
            protected $categories = ['foo', 'bar'];
        };

        $this->assertEquals(['foo', 'bar'], $fieldtype->categories());
    }

    /** @test */
    function it_can_be_flagged_as_hidden_from_the_fieldtype_selector()
    {
        $this->assertTrue((new TestFieldtype)->selectable());

        $fieldtype = new class extends Fieldtype {
            protected $selectable = false;
        };

        $this->assertFalse($fieldtype->selectable());
    }

    /** @test */
    function converts_to_an_array()
    {
        $fieldtype = new TestFieldtype;

        $this->assertEquals([
            'handle' => 'test',
            'title' => 'Test',
            'localizable' => true,
            'validatable' => true,
            'defaultable' => true,
            'selectable' => true,
            'categories' => ['text']
        ], $fieldtype->toArray());
    }

    /** @test */
    function it_gets_custom_validation_rules_as_an_array()
    {
        $this->assertEquals([], (new TestFieldtype)->rules());

        $arrayDefined = new class extends Fieldtype {
            protected $rules = ['required', 'min:2'];
        };
        $this->assertEquals(['required', 'min:2'], $arrayDefined->rules());

        $stringDefined = new class extends Fieldtype {
            protected $rules = 'required|min:2';
        };
        $this->assertEquals(['required', 'min:2'], $stringDefined->rules());
    }

    /** @test */
    function it_gets_extra_custom_validation_rules_as_an_array()
    {
        $this->assertEquals([], (new TestFieldtype)->rules());

        $arrayDefined = new class extends Fieldtype {
            protected $extraRules = [
                'extra.one' => ['required', 'min:2'],
                'extra.two' => ['array']
            ];
        };
        $this->assertEquals([
            'extra.one' => ['required', 'min:2'],
            'extra.two' => ['array']
        ], $arrayDefined->extraRules());

        $stringDefined = new class extends Fieldtype {
            protected $extraRules = [
                'extra.one' => 'required|min:2',
                'extra.two' => 'array'
            ];
        };
        $this->assertEquals([
            'extra.one' => ['required', 'min:2'],
            'extra.two' => ['array']
        ], $stringDefined->extraRules());
    }

    /** @test */
    function it_can_have_a_default_value()
    {
        $this->assertNull((new TestFieldtype)->defaultValue());

        $fieldtype = new class extends Fieldtype {
            protected $defaultValue = 'test';
        };

        $this->assertEquals('test', $fieldtype->defaultValue());
    }
}

class TestFieldtype extends Fieldtype
{
    //
}

class TestMultiWordFieldtype extends Fieldtype
{
    //
}

class TestMultiWordWithNoFieldtypeSuffix extends Fieldtype
{
    //
}
