<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\FormFields;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormFieldsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        tap(Form::make('contact')->formFields([
            'fields' => [
                ['handle' => 'intro', 'field' => ['type' => 'heading']],
                ['handle' => 'full_name', 'field' => ['type' => 'name']],
                ['handle' => 'email_address', 'field' => ['type' => 'email', 'display' => 'Email Address']],
                ['handle' => 'message', 'field' => ['type' => 'long_answer']],
            ],
        ]))->save();
    }

    #[Test]
    public function it_lists_the_fields_in_the_form()
    {
        $options = $this->fieldtype(['form' => 'contact'])->preload()['options'];

        $this->assertEquals([
            ['value' => 'full_name', 'label' => 'Full Name', 'icon' => 'user-avatar-flush', 'category' => 'contact'],
            ['value' => 'email_address', 'label' => 'Email Address', 'icon' => 'mail-sign-at', 'category' => 'contact'],
            ['value' => 'message', 'label' => 'Message', 'icon' => 'text-long', 'category' => 'text'],
        ], $options);
    }

    #[Test]
    public function it_only_lists_fields_in_the_configured_categories()
    {
        $options = $this->fieldtype(['form' => 'contact', 'categories' => ['contact', 'information']])->preload()['options'];

        $this->assertEquals(['intro', 'full_name', 'email_address'], array_column($options, 'value'));
    }

    #[Test]
    public function it_prefixes_the_option_values()
    {
        $options = $this->fieldtype(['form' => 'contact', 'prefix' => 'field:'])->preload()['options'];

        $this->assertEquals(['field:full_name', 'field:email_address', 'field:message'], array_column($options, 'value'));
    }

    #[Test]
    public function it_lists_the_fields_in_the_parent_form()
    {
        $fieldtype = (new FormFields)->setField(
            (new Field('test', ['type' => 'form_fields']))->setParent(Form::find('contact'))
        );

        $this->assertEquals(['full_name', 'email_address', 'message'], array_column($fieldtype->preload()['options'], 'value'));
    }

    #[Test]
    public function it_has_no_options_without_a_form()
    {
        $this->assertEquals([], $this->fieldtype([])->preload()['options']);
        $this->assertEquals([], $this->fieldtype(['form' => 'unknown'])->preload()['options']);
    }

    private function fieldtype(array $config): FormFields
    {
        return (new FormFields)->setField(new Field('test', array_merge($config, ['type' => 'form_fields'])));
    }
}
