<?php

namespace Tests\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Tests\TestCase;

class FormFieldTest extends TestCase
{
    #[Test]
    public function it_gets_the_display_value()
    {
        $this->assertEquals(
            'Test Display Value',
            (new FormField('test', ['display' => 'Test Display Value']))->display()
        );

        $this->assertEquals(
            'Test',
            (new Field('test', []))->display()
        );

        $this->assertEquals(
            'Test Multi Word Handle And No Explicit Display',
            (new FormField('test_multi_word_handle_and_no_explicit_display', []))->display()
        );
    }

    #[Test]
    public function it_gets_instructions()
    {
        $this->assertEquals(
            'The instructions',
            (new FormField('test', ['instructions' => 'The instructions']))->instructions()
        );

        $this->assertNull((new FormField('test', []))->instructions());
    }

    #[Test]
    public function it_gets_the_fieldtype()
    {
        $fieldtype = new class extends FormFieldtype
        {
            public function toFieldArray(): array
            {
                return [];
            }
        };

        FormFieldtypeRepository::shouldReceive('find')
            ->with('the_fieldtype')
            ->andReturnUsing(fn () => clone $fieldtype);
        $field = new FormField('test', ['type' => 'the_fieldtype', 'foo' => 'bar']);

        // The fieldtype from the repository should not have the field attached.
        $this->assertNull($fieldtype->field());

        // The fieldtype from the field should be an instance of that
        // fieldtype class, and should have the field attached.
        $this->assertInstanceOf(get_class($fieldtype), $field->fieldtype());
        $this->assertEquals($field->config(), $field->fieldtype()->field()->config());

        // Double check that the fieldtype from the repository still doesn't somehow have the field attached.
        $this->assertNull(FormFieldtypeRepository::find('the_fieldtype')->field());
    }

    #[Test]
    public function it_falls_back_to_fallback_fieldtype_for_unknown_form_fieldtypes()
    {
        $field = new FormField('test', ['type' => 'list', 'display' => 'Shopping List']);

        $this->assertInstanceOf(Fallback::class, $field->fieldtype());

        $this->assertEquals([
            'type' => 'list',
            'display' => 'Shopping List',
        ], $field->toFieldArray());
    }

    #[Test]
    public function it_converts_to_field_array()
    {
        $field = new FormField('email', ['type' => 'email', 'display' => 'Email Address', 'validate' => ['required']]);

        $this->assertEquals([
            'type' => 'text',
            'validate' => ['required', 'email'],
            'input_type' => 'email',
            'display' => 'Email Address',
            'placeholder' => null,
        ], $field->toFieldArray());
    }
}
