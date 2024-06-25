<?php

namespace Tests\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldTransformer;
use Statamic\Fields\Fieldtype;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldTransformerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function configToVue($config)
    {
        return FieldTransformer::toVue(['handle' => 'test', 'field' => $config])['config'];
    }

    #[Test]
    public function it_defaults_to_width_100()
    {
        // Will use configured width if set.
        $this->assertEquals(50, $this->configToVue(['width' => 50])['width']);

        // Defaults to width 100 if not set.
        $this->assertEquals(100, $this->configToVue([])['width']);
    }

    #[Test]
    public function it_defaults_to_localizable_false()
    {
        // Will use configured localizable if set.
        $this->assertTrue($this->configToVue(['localizable' => true])['localizable']);

        // Defaults to localizable false if not set.
        $this->assertFalse($this->configToVue([])['localizable']);
    }

    #[Test]
    public function it_normalizes_required_validation()
    {
        // It should replace `required: true` with `validate: ['required']`
        $this->assertArrayHasKey('validate', $config = $this->configToVue(['required' => true]));
        $this->assertArrayNotHasKey('required', $config = $this->configToVue(['required' => true]));
        $this->assertEquals(['required'], $config['validate']);

        // It should prepend `required`
        $this->assertEquals(
            ['required', 'email'],
            $this->configToVue(['required' => true, 'validate' => ['email']])['validate']
        );

        // It shouldn't prepend `required` if it already exists as a rule
        $this->assertEquals(
            ['min:3', 'required'],
            $this->configToVue(['required' => true, 'validate' => ['min:3', 'required']])['validate']
        );

        // It should normalize to an array and prepend `required`
        $this->assertEquals(
            ['required', 'min:3', 'email'],
            $this->configToVue(['required' => true, 'validate' => 'min:3|email'])['validate']
        );

        // It should normalize to an array but shouldn't prepend `required` if it already exists as a rule
        $this->assertEquals(
            ['min:3', 'required', 'email'],
            $this->configToVue(['required' => true, 'validate' => 'min:3|required|email'])['validate']
        );
    }

    #[Test]
    public function it_removes_redundant_config_options()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function configFieldItems(): array
            {
                return [
                    'input_type' => ['type' => 'text', 'default' => 'text'],
                    'character_limit' => ['type' => 'integer', 'default' => null],
                    'max_items' => ['type' => 'integer', 'default' => 1, 'force_in_config' => true],
                ];
            }
        };
        $fieldtype::register();

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'test',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                // Fieldtype config options
                'input_type' => 'text', // The default.
                'character_limit' => 100, // This one has been changed.
                'max_items' => 1, // Even though it matches the default, it has been flagged to be explicitly kept.
                'foo' => 'bar', // Manually added by user.

                // Common field options
                'instructions_position' => 'above', // The default.
                'listable' => true, // This one has been changed.
            ],
        ]);

        $this->assertEquals([
            'character_limit' => 100,
            'listable' => true,
            'max_items' => 1,
            'foo' => 'bar',
        ], $fromVue['field']);
    }

    #[Test]
    public function it_removes_full_width_from_field_config()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => ['width' => 100, 'display' => 'Test'],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => ['display' => 'Test'],
        ], $fromVue);

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => ['width' => 50, 'display' => 'Test'],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => ['width' => 50, 'display' => 'Test'],
        ], $fromVue);
    }

    #[Test]
    public function it_removes_localizable_false_from_field_config()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => ['display' => 'Test', 'localizable' => false],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => ['display' => 'Test'],
        ], $fromVue);

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => ['display' => 'Test', 'localizable' => true],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => ['display' => 'Test', 'localizable' => true],
        ], $fromVue);
    }

    #[Test]
    public function it_removes_duplicate_from_field_config()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => ['display' => 'Test', 'duplicate' => true],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => ['display' => 'Test'],
        ], $fromVue);
    }

    #[Test]
    public function sets_and_fields_are_always_at_the_end_of_field_configs()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => [
                'display' => 'Test',
                'sets' => ['set_group' => ['sets' => ['set' => ['fields' => ['import' => 'seo']]]]],
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
            ],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => [
                'display' => 'Test',
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
                'sets' => ['set_group' => ['sets' => ['set' => ['fields' => ['import' => 'seo']]]]],
            ],
        ], $fromVue);

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => [
                'display' => 'Test',
                'fields' => [['import' => 'seo'], ['handle' => 'foo']],
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
            ],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => [
                'display' => 'Test',
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
                'fields' => [['import' => 'seo'], ['handle' => 'foo']],
            ],
        ], $fromVue);
    }

    #[Test]
    public function blank_instructions_and_icon_are_removed_from_set_groups()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => [
                'display' => 'Test',
                'sets' => ['set_group' => [
                    'display' => 'Set Group',
                    'instructions' => null,
                    'icon' => null,
                    'sets' => ['set' => [
                        'display' => 'Set',
                        'instructions' => null,
                        'icon' => null,
                        'fields' => ['import' => 'seo'],
                    ]],
                ]],
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
            ],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => [
                'display' => 'Test',
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
                'sets' => ['set_group' => [
                    'display' => 'Set Group',
                    'sets' => ['set' => [
                        'display' => 'Set',
                        'fields' => ['import' => 'seo'],
                    ]],
                ]],
            ],
        ], $fromVue);

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text', 'handle' => 'test', 'type' => 'inline', 'config' => [
                'display' => 'Test',
                'sets' => ['set_group' => [
                    'display' => 'Set Group',
                    'instructions' => 'This is a set group.',
                    'icon' => null,
                    'sets' => ['set' => [
                        'display' => 'Set',
                        'instructions' => null,
                        'icon' => 'date',
                        'fields' => ['import' => 'seo'],
                    ]],
                ]],
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
            ],
        ]);

        $this->assertEquals([
            'handle' => 'test',
            'field' => [
                'display' => 'Test',
                'instructions' => 'Some instructions',
                'listable' => true,
                'foo' => 'bar',
                'sets' => ['set_group' => [
                    'display' => 'Set Group',
                    'instructions' => 'This is a set group.',
                    'sets' => ['set' => [
                        'display' => 'Set',
                        'icon' => 'date',
                        'fields' => ['import' => 'seo'],
                    ]],
                ]],
            ],
        ], $fromVue);
    }

    /**
     * @see https://github.com/statamic/cms/issues/10056
     */
    #[Test]
    public function it_doesnt_remove_max_items_from_form_fieldtype()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'form',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'foo' => 'bar',
                'max_items' => 1,
            ],
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'max_items' => 1,
        ], $fromVue['field']);
    }

    /**
     * @see https://github.com/statamic/cms/issues/10050
     */
    #[Test]
    public function it_ensures_the_asset_container_is_saved_on_the_assets_fieldtype()
    {
        AssetContainer::make('test')->save();

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'assets',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'foo' => 'bar',
                'container' => 'test',
            ],
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'container' => 'test',
        ], $fromVue['field']);
    }

    /**
     * @see https://github.com/statamic/cms/issues/10040
     */
    #[Test]
    public function it_saves_a_toggle_as_false_where_the_default_is_true()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'replicator_preview' => false,
                'duplicate' => false,
            ],
        ]);

        $this->assertEquals([
            'replicator_preview' => false,
            'duplicate' => false,
        ], $fromVue['field']);
    }

    #[Test]
    public function it_supports_addon_linked_fields()
    {
        $fieldset = tap(new Fieldset)
            ->setHandle('addon::some_fieldset')
            ->setContents(['fields' => [
                [
                    'handle' => 'field1',
                    'field' => ['type' => 'text', 'foo' => 'bar'],
                ],
            ]]);

        \Statamic\Facades\Fieldset::shouldReceive('all')->andReturn(collect([
            'addon::some' => $fieldset,
        ]));

        $this->assertEquals([
            'type' => 'text',
            'foo' => 'bar',
            'width' => 100,
            'localizable' => false,
        ], $this->configToVue('addon::some_fieldset.field1'));
    }
}
