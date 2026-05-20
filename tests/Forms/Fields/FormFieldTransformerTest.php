<?php

namespace Tests\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldTransformer;
use Statamic\Forms\Fields\FormFieldtype;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormFieldTransformerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function configToVue($config)
    {
        return FormFieldTransformer::toVue(['handle' => 'test', 'field' => $config])['config'];
    }

    #[Test]
    public function it_defaults_to_width_100()
    {
        $this->assertEquals(50, $this->configToVue(['type' => 'short_answer', 'width' => 50])['width']);

        $this->assertEquals(100, $this->configToVue(['type' => 'short_answer'])['width']);
    }

    #[Test]
    public function it_defaults_to_hidden_false()
    {
        $this->assertTrue($this->configToVue(['type' => 'short_answer', 'hidden' => true])['hidden']);

        $this->assertFalse($this->configToVue(['type' => 'short_answer'])['hidden']);
    }

    #[Test]
    public function it_normalizes_required_validation()
    {
        // It should replace `required: true` with `validate: ['required']`
        $this->assertArrayHasKey('validate', $config = $this->configToVue(['type' => 'short_answer', 'required' => true]));
        $this->assertArrayNotHasKey('required', $config);
        $this->assertEquals(['required'], $config['validate']);

        // It should prepend `required`
        $this->assertEquals(
            ['required', 'email'],
            $this->configToVue(['type' => 'short_answer', 'required' => true, 'validate' => ['email']])['validate']
        );

        // It shouldn't prepend `required` if it already exists as a rule
        $this->assertEquals(
            ['min:3', 'required'],
            $this->configToVue(['type' => 'short_answer', 'required' => true, 'validate' => ['min:3', 'required']])['validate']
        );

        // It should normalize to an array and prepend `required`
        $this->assertEquals(
            ['required', 'min:3', 'email'],
            $this->configToVue(['type' => 'short_answer', 'required' => true, 'validate' => 'min:3|email'])['validate']
        );

        // It should normalize to an array but shouldn't prepend `required` if it already exists as a rule
        $this->assertEquals(
            ['min:3', 'required', 'email'],
            $this->configToVue(['type' => 'short_answer', 'required' => true, 'validate' => 'min:3|required|email'])['validate']
        );
    }

    #[Test]
    public function it_converts_inline_field_to_vue()
    {
        $field = [
            'handle' => 'my_field',
            'field' => [
                'type' => 'short_answer',
                'display' => 'My Field',
                'placeholder' => 'Enter text',
            ],
        ];

        $vue = FormFieldTransformer::toVue($field);

        $this->assertEquals('my_field', $vue['handle']);
        $this->assertEquals('inline', $vue['type']);
        $this->assertEquals('short_answer', $vue['fieldtype']);
        $this->assertEquals('My Field', $vue['config']['display']);
        $this->assertEquals('Enter text', $vue['config']['placeholder']);
        $this->assertEquals(100, $vue['config']['width']);
        $this->assertFalse($vue['config']['hidden']);
        $this->assertArrayHasKey('icon', $vue);
        $this->assertArrayHasKey('preview', $vue);
    }

    #[Test]
    public function it_converts_from_vue_to_inline_field()
    {
        $submitted = [
            'handle' => 'my_field',
            'type' => 'inline',
            'fieldtype' => 'short_answer',
            'config' => [
                'type' => 'short_answer',
                'display' => 'My Field',
                'placeholder' => 'Enter text',
                'width' => 100,
                'hidden' => false,
            ],
        ];

        $field = FormFieldTransformer::fromVue($submitted);

        $this->assertEquals('my_field', $field['handle']);
        $this->assertIsArray($field['field']);
        $this->assertEquals('My Field', $field['field']['display']);
        $this->assertEquals('Enter text', $field['field']['placeholder']);
        // Width 100 should be removed as it's the default
        $this->assertArrayNotHasKey('width', $field['field']);
        // Hidden false should be removed as it's the default
        $this->assertArrayNotHasKey('hidden', $field['field']);
    }

    #[Test]
    public function it_removes_full_width_from_field_config()
    {
        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'short_answer',
            'handle' => 'test',
            'type' => 'inline',
            'config' => ['type' => 'short_answer', 'width' => 100, 'display' => 'Test'],
        ]);

        $this->assertEquals('test', $fromVue['handle']);
        $this->assertEquals('Test', $fromVue['field']['display']);
        $this->assertArrayNotHasKey('width', $fromVue['field']);

        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'short_answer',
            'handle' => 'test',
            'type' => 'inline',
            'config' => ['type' => 'short_answer', 'width' => 50, 'display' => 'Test'],
        ]);

        $this->assertEquals('test', $fromVue['handle']);
        $this->assertEquals(50, $fromVue['field']['width']);
        $this->assertEquals('Test', $fromVue['field']['display']);
    }

    #[Test]
    public function it_removes_hidden_false_from_field_config()
    {
        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'short_answer',
            'handle' => 'test',
            'type' => 'inline',
            'config' => ['type' => 'short_answer', 'display' => 'Test', 'hidden' => false],
        ]);

        $this->assertEquals('test', $fromVue['handle']);
        $this->assertEquals('Test', $fromVue['field']['display']);
        $this->assertArrayNotHasKey('hidden', $fromVue['field']);

        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'short_answer',
            'handle' => 'test',
            'type' => 'inline',
            'config' => ['type' => 'short_answer', 'display' => 'Test', 'hidden' => true],
        ]);

        $this->assertEquals('test', $fromVue['handle']);
        $this->assertEquals('Test', $fromVue['field']['display']);
        $this->assertTrue($fromVue['field']['hidden']);
    }

    #[Test]
    public function it_removes_icon_from_field_config()
    {
        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'short_answer',
            'handle' => 'test',
            'type' => 'inline',
            'config' => ['type' => 'short_answer', 'display' => 'Test', 'icon' => 'text'],
        ]);

        $this->assertEquals('test', $fromVue['handle']);
        $this->assertEquals('Test', $fromVue['field']['display']);
        $this->assertArrayNotHasKey('icon', $fromVue['field']);
    }

    #[Test]
    public function it_removes_redundant_config_options()
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'test_form_field';

            public function configFieldItems(): array
            {
                return [
                    'placeholder' => ['type' => 'text', 'default' => ''],
                    'character_limit' => ['type' => 'integer', 'default' => null],
                    'max_items' => ['type' => 'integer', 'default' => 1, 'force_in_config' => true],
                ];
            }

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $formFieldtype::register();

        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'test_form_field',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'type' => 'test_form_field',
                // Fieldtype config options
                'placeholder' => '', // The default.
                'character_limit' => 100, // This one has been changed.
                'max_items' => 1, // Even though it matches the default, it has been flagged to be explicitly kept.
                'foo' => 'bar', // Manually added by user.

                // Common field options
                'display' => 'Test', // Not default, should be kept.
            ],
        ]);

        $this->assertEquals(100, $fromVue['field']['character_limit']);
        $this->assertEquals(1, $fromVue['field']['max_items']);
        $this->assertEquals('bar', $fromVue['field']['foo']);
        $this->assertEquals('Test', $fromVue['field']['display']);
        // Default values should be removed
        $this->assertArrayNotHasKey('placeholder', $fromVue['field']);
    }

    #[Test]
    public function it_preserves_custom_config_values()
    {
        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'short_answer',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'type' => 'short_answer',
                'display' => 'Test Field',
                'instructions' => 'Enter your name',
                'placeholder' => 'Your name here',
                'custom_option' => 'custom_value',
            ],
        ]);

        $this->assertEquals('Test Field', $fromVue['field']['display']);
        $this->assertEquals('Enter your name', $fromVue['field']['instructions']);
        $this->assertEquals('Your name here', $fromVue['field']['placeholder']);
        $this->assertEquals('custom_value', $fromVue['field']['custom_option']);
    }

    #[Test]
    public function it_includes_preview_in_vue_output()
    {
        $field = [
            'handle' => 'my_field',
            'field' => [
                'type' => 'short_answer',
            ],
        ];

        $vue = FormFieldTransformer::toVue($field);

        $this->assertArrayHasKey('preview', $vue);
        $this->assertIsArray($vue['preview']);
        $this->assertArrayHasKey('config', $vue['preview']);
        $this->assertArrayHasKey('value', $vue['preview']);
        $this->assertArrayHasKey('meta', $vue['preview']);
    }

    #[Test]
    public function it_saves_a_toggle_as_false_where_the_default_is_true()
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'test_toggle_field';

            public function configFieldItems(): array
            {
                return [
                    'show_preview' => ['type' => 'toggle', 'default' => true],
                    'allow_multiple' => ['type' => 'toggle', 'default' => true],
                ];
            }

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $formFieldtype::register();

        $fromVue = FormFieldTransformer::fromVue([
            'fieldtype' => 'test_toggle_field',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'type' => 'test_toggle_field',
                'display' => 'Test',
                'show_preview' => false,
                'allow_multiple' => false,
            ],
        ]);

        $this->assertEquals('Test', $fromVue['field']['display']);
        $this->assertFalse($fromVue['field']['show_preview']);
        $this->assertFalse($fromVue['field']['allow_multiple']);
    }
}
