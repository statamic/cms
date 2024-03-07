<?php

namespace Tests\Fieldtypes;

use Statamic\Fields\ConfigField;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Sets;
use Statamic\Statamic;
use Tests\TestCase;

class SetsTest extends TestCase
{
    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_preprocesses_with_empty_value()
    {
        $field = (new Field('test', [
            'type' => 'sets',
        ]));

        $this->assertEquals([], $field->preProcess()->value());
    }

    /** @test */
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

        $this->assertEquals([
            [
                'handle' => 'alfa',
                'display' => 'Alfa',
                'instructions' => 'Alfa instructions',
                'icon' => 'alfa-icon',
                'sets' => [
                    [
                        'id' => 'one',
                        'handle' => 'one',
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'fields' => [
                            [
                                'display' => 'Field One',
                                'handle' => 'field_one',
                                'type' => 'text',
                                'input_type' => 'text',
                                'placeholder' => null,
                                'default' => null,
                                'character_limit' => 0,
                                'prepend' => null,
                                'append' => null,
                                'antlers' => false,
                                'component' => 'text',
                                'prefix' => null,
                                'instructions' => null,
                                'required' => false,
                                'visibility' => 'visible',
                                'read_only' => false,
                                'always_save' => false,
                                'autocomplete' => null,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'handle' => 'bravo',
                'sets' => [
                    [
                        'id' => 'two',
                        'handle' => 'two',
                        'fields' => [
                            [
                                'display' => 'Field Two',
                                'handle' => 'field_two',
                                'type' => 'text',
                                'input_type' => 'text',
                                'placeholder' => null,
                                'default' => null,
                                'character_limit' => 0,
                                'prepend' => null,
                                'append' => null,
                                'antlers' => false,
                                'component' => 'text',
                                'prefix' => null,
                                'instructions' => null,
                                'required' => false,
                                'visibility' => 'visible',
                                'read_only' => false,
                                'always_save' => false,
                                'autocomplete' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], $field->preProcess()->value());
    }

    /** @test */
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

        $this->assertEquals([
            [
                'handle' => 'main',
                'sets' => [
                    [
                        'id' => 'one',
                        'handle' => 'one',
                        'display' => 'One',
                        'instructions' => 'One instructions',
                        'icon' => 'one-icon',
                        'fields' => [
                            [
                                'display' => 'Field One',
                                'handle' => 'field_one',
                                'type' => 'text',
                                'input_type' => 'text',
                                'placeholder' => null,
                                'default' => null,
                                'character_limit' => 0,
                                'prepend' => null,
                                'append' => null,
                                'antlers' => false,
                                'component' => 'text',
                                'prefix' => null,
                                'instructions' => null,
                                'required' => false,
                                'visibility' => 'visible',
                                'read_only' => false,
                                'always_save' => false,
                                'autocomplete' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], $field->preProcess()->value());
    }

    /** @test */
    public function it_preprocesses_for_config_with_empty_value()
    {
        $field = (new ConfigField('test', [
            'type' => 'sets',
        ]));

        $this->assertEquals([], $field->preProcess()->value());
    }

    /** @test */
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

    /** @test */
    public function it_provides_statamic_plump_icons_to_script_by_default()
    {
        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertNull($jsonVariables['setIconsDirectory']);
        $this->assertEquals('plump', $jsonVariables['setIconsFolder']);
    }

    /** @test */
    public function it_can_provide_custom_user_icons_subfolder()
    {
        Sets::setIconsDirectory(folder: 'light');

        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertNull($jsonVariables['setIconsDirectory']);
        $this->assertEquals('light', $jsonVariables['setIconsFolder']);
    }

    /** @test */
    public function it_can_provide_custom_user_icons_directory()
    {
        Sets::setIconsDirectory($customDir = resource_path());

        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertEquals($customDir, $jsonVariables['setIconsDirectory']);
        $this->assertEquals(null, $jsonVariables['setIconsFolder']);
    }

    /** @test */
    public function it_can_provide_custom_user_icons_directory_and_sub_folder()
    {
        Sets::setIconsDirectory($customDir = base_path(), $customSubFolder = 'resources');

        $jsonVariables = Statamic::jsonVariables(request());

        $this->assertEquals($customDir, $jsonVariables['setIconsDirectory']);
        $this->assertEquals($customSubFolder, $jsonVariables['setIconsFolder']);
    }
}
