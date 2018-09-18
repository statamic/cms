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
            'localizable' => true,
            'validatable' => true,
            'defaultable' => true,
            'selectable' => true,
            'categories' => ['text']
        ], $fieldtype->toArray());
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
