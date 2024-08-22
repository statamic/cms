<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\ConfigField;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Sets;
use Statamic\Statamic;
use Tests\TestCase;

class SetsTest extends TestCase
{
    #[Test]
    public function it_preprocesses_with_groups()
    {
        $field = (new Field('test', [
            'type' => 'sets',
        ]))->setValue([
            'alfa' => [
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sets' => [
                    'one' => [
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'hide' => true,
                        'fields' => [
                            ['handle' => 'field_one', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
            // This one has the minimum required keys so that we can check that they get filled with nulls instead of erroring.
            'bravo' => [
                'sets' => [
                    'two' => [
                        'fields' => [
                            ['handle' => 'field_two', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            [
                '_id' => 'group-alfa',
                'handle' => 'alfa',
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sections' => [
                    [
                        '_id' => 'group-alfa-section-one',
                        'handle' => 'one',
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'hide' => true,
                        'fields' => [
                            [
                                '_id' => 'group-alfa-section-one-0',
                                'handle' => 'field_one',
                                'type' => 'inline',
                                'fieldtype' => 'text',
                                'icon' => 'text',
                                'config' => [
                                    'type' => 'text',
                                    'width' => 100,
                                    'localizable' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                '_id' => 'group-bravo',
                'handle' => 'bravo',
                'display' => null,
                'instructions' => null,
                'icon' => null,
                'sections' => [
                    [
                        '_id' => 'group-bravo-section-two',
                        'handle' => 'two',
                        'display' => null,
                        'instructions' => null,
                        'icon' => null,
                        'hide' => null,
                        'fields' => [
                            [
                                '_id' => 'group-bravo-section-two-0',
                                'handle' => 'field_two',
                                'type' => 'inline',
                                'fieldtype' => 'text',
                                'icon' => 'text',
                                'config' => [
                                    'type' => 'text',
                                    'width' => 100,
                                    'localizable' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses_without_groups()
    {
        $field = (new Field('test', [
            'type' => 'sets',
        ]))->setValue([
            'one' => [
                'display' => 'One',
                'instructions' => 'One instructions',
                'icon' => 'one-icon',
                'fields' => [
                    ['handle' => 'field_one', 'field' => ['type' => 'text']],
                ],
            ],
        ]);

        $this->assertEquals([
            [
                '_id' => 'group-main',
                'handle' => 'main',
                'display' => 'Main',
                'instructions' => null,
                'icon' => null,
                'sections' => [
                    [
                        '_id' => 'group-main-section-one',
                        'handle' => 'one',
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'hide' => null,
                        'fields' => [
                            [
                                '_id' => 'group-main-section-one-0',
                                'handle' => 'field_one',
                                'type' => 'inline',
                                'fieldtype' => 'text',
                                'icon' => 'text',
                                'config' => [
                                    'type' => 'text',
                                    'width' => 100,
                                    'localizable' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses_with_empty_value()
    {
        $field = (new Field('test', [
            'type' => 'sets',
        ]));

        $this->assertEquals([], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses_for_config_with_groups()
    {
        $field = (new ConfigField('test', [
            'type' => 'sets',
        ]))->setValue([
            'alfa' => [
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sets' => [
                    'one' => [
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'fields' => [
                            ['handle' => 'field_one', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
            // This one has the minimum required keys so that we can check that they get filled with nulls instead of erroring.
            'bravo' => [
                'sets' => [
                    'two' => [
                        'fields' => [
                            ['handle' => 'field_two', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            [
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sets' => [
                    [
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'fields' => [
                            [
                                'display' => 'Field One',
                                'hide_display' => false,
                                'handle' => 'field_one',
                                'instructions' => null,
                                'instructions_position' => 'above',
                                'listable' => 'hidden',
                                'sortable' => true,
                                'visibility' => 'visible',
                                'replicator_preview' => true,
                                'duplicate' => true,
                                'type' => 'text',
                                'input_type' => 'text',
                                'placeholder' => null,
                                'default' => null,
                                'character_limit' => 0,
                                'autocomplete' => null,
                                'prepend' => null,
                                'append' => null,
                                'antlers' => false,
                                'component' => 'text',
                                'prefix' => null,
                                'required' => false,
                                'read_only' => false,
                                'always_save' => false,
                            ],
                        ],
                        'handle' => 'one',
                        'id' => 'one',
                    ],
                ],
                'handle' => 'alfa',
            ],
            [
                'sets' => [
                    [
                        'fields' => [
                            [
                                'display' => 'Field Two',
                                'hide_display' => false,
                                'handle' => 'field_two',
                                'instructions' => null,
                                'instructions_position' => 'above',
                                'listable' => 'hidden',
                                'sortable' => true,
                                'visibility' => 'visible',
                                'replicator_preview' => true,
                                'duplicate' => true,
                                'type' => 'text',
                                'input_type' => 'text',
                                'placeholder' => null,
                                'default' => null,
                                'character_limit' => 0,
                                'autocomplete' => null,
                                'prepend' => null,
                                'append' => null,
                                'antlers' => false,
                                'component' => 'text',
                                'prefix' => null,
                                'required' => false,
                                'read_only' => false,
                                'always_save' => false,
                            ],
                        ],
                        'handle' => 'two',
                        'id' => 'two',
                    ],
                ],
                'handle' => 'bravo',
            ],
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses_for_config_without_groups()
    {
        $field = (new ConfigField('test', [
            'type' => 'sets',
        ]))->setValue([
            'one' => [
                'display' => 'One',
                'instructions' => 'One instructions',
                'icon' => 'one-icon',
                'fields' => [
                    ['handle' => 'field_one', 'field' => ['type' => 'text']],
                ],
            ],
        ]);

        $this->assertSame([
            [
                'sets' => [
                    [
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'fields' => [
                            [
                                'display' => 'Field One',
                                'hide_display' => false,
                                'handle' => 'field_one',
                                'instructions' => null,
                                'instructions_position' => 'above',
                                'listable' => 'hidden',
                                'sortable' => true,
                                'visibility' => 'visible',
                                'replicator_preview' => true,
                                'duplicate' => true,
                                'type' => 'text',
                                'input_type' => 'text',
                                'placeholder' => null,
                                'default' => null,
                                'character_limit' => 0,
                                'autocomplete' => null,
                                'prepend' => null,
                                'append' => null,
                                'antlers' => false,
                                'component' => 'text',
                                'prefix' => null,
                                'required' => false,
                                'read_only' => false,
                                'always_save' => false,
                            ],
                        ],
                        'handle' => 'one',
                        'id' => 'one',
                    ],
                ],
                'handle' => 'main',
            ],
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses_for_config_with_empty_value()
    {
        $field = (new ConfigField('test', [
            'type' => 'sets',
        ]));

        $this->assertEquals([], $field->preProcess()->value());
    }

    #[Test]
    public function it_processes()
    {
        $field = (new Field('test', [
            'type' => 'sets',
        ]))->setValue([
            [
                'handle' => 'alfa',
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sections' => [
                    [
                        'handle' => 'one',
                        'display' => 'One',
                        'instructions' => 'One Instructions',
                        'icon' => 'one-icon',
                        'hide' => false,
                        'fields' => [
                            [
                                'handle' => 'field_one',
                                'type' => 'inline',
                                'config' => [
                                    'type' => 'text',
                                    'foo' => 'bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'alfa' => [
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sets' => [
                    'one' => [
                        'display' => 'One',
                        'instructions' => 'One Instructions',
                        'icon' => 'one-icon',
                        'hide' => false,
                        'fields' => [
                            [
                                'handle' => 'field_one',
                                'field' => [
                                    'type' => 'text',
                                    'foo' => 'bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $field->process()->value());
    }

    #[Test]
    public function it_provides_statamic_plump_icons_to_script_by_default()
    {
        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertNull($jsonVariables['setIconsDirectory']);
        $this->assertEquals('plump', $jsonVariables['setIconsFolder']);
    }

    #[Test]
    public function it_can_provide_custom_user_icons_subfolder()
    {
        Sets::setIconsDirectory(folder: 'light');

        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertNull($jsonVariables['setIconsDirectory']);
        $this->assertEquals('light', $jsonVariables['setIconsFolder']);
    }

    #[Test]
    public function it_can_provide_custom_user_icons_directory()
    {
        Sets::setIconsDirectory($customDir = resource_path());

        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertEquals($customDir, $jsonVariables['setIconsDirectory']);
        $this->assertEquals(null, $jsonVariables['setIconsFolder']);
    }

    #[Test]
    public function it_can_provide_custom_user_icons_directory_and_sub_folder()
    {
        Sets::setIconsDirectory($customDir = base_path(), $customSubFolder = 'resources');

        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertEquals($customDir, $jsonVariables['setIconsDirectory']);
        $this->assertEquals($customSubFolder, $jsonVariables['setIconsFolder']);
    }
}
