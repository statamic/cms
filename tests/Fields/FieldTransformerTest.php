<?php

namespace Tests\Fields;

use Statamic\Facades\AssetContainer;
use Statamic\Fields\FieldTransformer;
use Tests\TestCase;

class FieldTransformerTest extends TestCase
{
    protected function configToVue($config)
    {
        return FieldTransformer::toVue(['handle' => 'test', 'field' => $config])['config'];
    }

    /** @test */
    public function it_defaults_to_width_100()
    {
        // Will use configured width if set.
        $this->assertEquals(50, $this->configToVue(['width' => 50])['width']);

        // Defaults to width 100 if not set.
        $this->assertEquals(100, $this->configToVue([])['width']);
    }

    /** @test */
    public function it_defaults_to_localizable_false()
    {
        // Will use configured localizable if set.
        $this->assertTrue($this->configToVue(['localizable' => true])['localizable']);

        // Defaults to localizable false if not set.
        $this->assertFalse($this->configToVue([])['localizable']);
    }

    /** @test */
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

    /** @test */
    public function it_removes_redundant_config_options()
    {
        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'text',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                // Fieldtype config options
                'input_type' => 'text', // The default.
                'icon' => 'text', // The default.
                'character_limit' => 100, // This one has been changed.
                'foo' => 'bar', // Manually added by user.

                // Common field options
                'instructions_position' => 'above', // The default.
                'listable' => true, // This one has been changed.
            ],
        ]);

        $this->assertEquals([
            'character_limit' => 100,
            'listable' => true,
            'foo' => 'bar',
        ], $fromVue['field']);
    }

    /** @test */
    public function it_doesnt_remove_container_config_option_from_assets_field()
    {
        AssetContainer::make('assets')->save();

        $fromVue = FieldTransformer::fromVue([
            'fieldtype' => 'assets',
            'handle' => 'test',
            'type' => 'inline',
            'config' => [
                'mode' => 'list',  // The default.
                'icon' => 'assets', // The default.
                'foo' => 'bar', // Manually added by user.
                'container' => 'assets', // The default, but it shouldn't get removed.
            ],
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'container' => 'assets',
        ], $fromVue['field']);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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
}
