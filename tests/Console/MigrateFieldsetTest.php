<?php

namespace Tests\Console;

use Statamic\API\YAML;
use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Console\Processes\Composer;
use Tests\Console\Foundation\InteractsWithConsole;

class MigrateFieldsetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->files->makeDirectory(base_path('resources/blueprints'));
    }

    public function tearDown(): void
    {
        $this->files->deleteDirectory(base_path('resources/blueprints'));

        parent::tearDown();
    }

    /** @test */
    function it_can_migrate_a_fieldset_to_a_blueprint()
    {
        $blueprint = $this->migrateFieldsetToBlueprint([
            'title' => 'Gallery',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'width' => 50
                ],
                'slug' => [
                    'type' => 'text',
                    'width' => 50
                ]
            ]
        ]);

        $this->assertEquals($blueprint, [
            'title' => 'Gallery',
            'fields' => [
                [
                    'handle' => 'title',
                    'field' => [
                        'type' => 'text',
                        'width' => 50
                    ]
                ],
                [
                    'handle' => 'slug',
                    'field' => [
                        'type' => 'text',
                        'width' => 50
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    function it_assumes_type_text()
    {
        $blueprint = $this->migrateFieldsetToBlueprint([
            'title' => 'Gallery',
            'fields' => [
                'title' => [
                    'width' => 50
                ]
            ]
        ]);

        $this->assertEquals($blueprint, [
            'title' => 'Gallery',
            'fields' => [
                [
                    'handle' => 'title',
                    'field' => [
                        'type' => 'text',
                        'width' => 50
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    function it_can_migrate_nested_fields()
    {
        $blueprint = $this->migrateFieldsetToBlueprint([
            'title' => 'Gallery',
            'fields' => [
                'prices' => [
                    'type' => 'grid',
                    'fields' => [
                        'label' => [
                            'type' => 'text'
                        ],
                        'cost' => [
                            'type' => 'currency'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals($blueprint, [
            'title' => 'Gallery',
            'fields' => [
                [
                    'handle' => 'prices',
                    'field' => [
                        'type' => 'grid',
                        'fields' => [
                            [
                                'handle' => 'label',
                                'field' => [
                                    'type' => 'text'
                                ]
                            ],
                            [
                                'handle' => 'cost',
                                'field' => [
                                    'type' => 'currency'
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    function it_can_migrate_nested_sets_of_fields()
    {
        $blueprint = $this->migrateFieldsetToBlueprint([
            'title' => 'Gallery',
            'fields' => [
                'prices' => [
                    'type' => 'replicator',
                    'sets' => [
                        'main' => [
                            'fields' => [
                                'label' => [
                                    'type' => 'text'
                                ],
                                'cost' => [
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals($blueprint, [
            'title' => 'Gallery',
            'fields' => [
                [
                    'handle' => 'prices',
                    'field' => [
                        'type' => 'replicator',
                        'sets' => [
                            'main' => [
                                'fields' => [
                                    [
                                        'handle' => 'label',
                                        'field' => [
                                            'type' => 'text'
                                        ]
                                    ],
                                    [
                                        'handle' => 'cost',
                                        'field' => [
                                            'type' => 'currency'
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    function it_migrates_field_type_first()
    {
        $blueprint = $this->migrateFieldsetToBlueprint([
            'title' => 'Gallery',
            'fields' => [
                'title' => [
                    'width' => 50,
                    'type' => 'text'
                ]
            ]
        ]);

        $this->assertEquals($blueprint, [
            'title' => 'Gallery',
            'fields' => [
                [
                    'handle' => 'title',
                    'field' => [
                        'type' => 'text',
                        'width' => 50
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    function it_migrates_field_conditions()
    {
        $blueprint = $this->migrateFieldsetToBlueprint([
            'title' => 'Post',
            'fields' => [
                'author_name' => [
                    'type' => 'text',
                    'show_when' => [
                        'has_author' => 'not null'
                    ],
                ],
                'author_address' => [
                    'type' => 'text',
                    'show_when' => 'myCustomConditionMethod'
                ],
                'author_explain_yourself' => [
                    'type' => 'text',
                    'hide_when' => [
                        'favourite_food' => 'lasagna',
                        'or_favourite_colour' => 'red'
                    ]
                ]
            ]
        ]);

        $this->assertEquals($blueprint, [
            'title' => 'Post',
            'fields' => [
                [
                    'handle' => 'author_name',
                    'field' => [
                        'type' => 'text',
                        'show_when' => [
                            'has_author' => 'not empty'
                        ]
                    ]
                ],
                [
                    'handle' => 'author_address',
                    'field' => [
                        'type' => 'text',
                        'show_when' => 'myCustomConditionMethod'
                    ]
                ],
                [
                    'handle' => 'author_explain_yourself',
                    'field' => [
                        'type' => 'text',
                        'hide_when_any' => [
                            'favourite_food' => 'lasagna',
                            'favourite_colour' => 'red'
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function migrateFieldsetToBlueprint($fieldsetConfig)
    {
        $path = base_path('resources/blueprints/post.yaml');

        $this->files->put($path, YAML::dump($fieldsetConfig));

        $this->artisan('statamic:migrate:fieldset', ['handle' => 'post']);

        return YAML::parse($this->files->get($path));
    }
}
