<?php

namespace Tests\Preferences;

use Tests\TestCase;
use Statamic\Preferences\HasPreferences;

class HasPreferencesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->person = new Person;
    }

    /** @test */
    function it_can_get_and_set_array_of_preferences()
    {
        $preferences = ['language' => 'english'];

        $this->assertEquals([], $this->person->preferences());

        $this->person->preferences($preferences);

        $this->assertEquals($preferences, $this->person->preferences());
    }

    /** @test */
    function it_can_set_array_of_preferences()
    {
        $this->person->preferences([
            'language' => 'english',
            'color' => 'red'
        ]);

        $this->person->setPreferences([
            'language' => 'french',
            'music' => 'metal'
        ]);

        $expected = [
            'language' => 'french',
            'color' => 'red',
            'music' => 'metal'
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_set_a_single_preference()
    {
        $this->person->setPreference('collection.columns', ['date', 'title']);

        $expected = [
            'collection' => [
                'columns' => [
                    'date',
                    'title'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_remove_a_single_preference()
    {
        $this->person->preferences([
            'collection' => [
                'columns' => [
                    'date',
                    'title'
                ],
                'filters' => [
                    'published'
                ]
            ]
        ]);

        $this->person->removePreference('collection.columns');

        $expected = [
            'collection' => [
                'filters' => [
                    'published'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_remove_a_single_preference_array_value()
    {
        $this->person->preferences([
            'collection' => [
                'columns' => [
                    'date',
                    'title',
                    'slug'
                ],
            ],
            'favorites' => [
                [
                    'name' => 'Updates',
                    'url' => 'https://worldwideweb.com/cp/updater/statamic'
                ],
                [
                    'name' => 'Blog',
                    'url' => 'https://worldwideweb.com/cp/collections/blog'
                ]
            ]
        ]);

        $this->person->removePreference('collection.columns', 'date');
        $this->person->removePreference('collection.columns', 'slug');
        $this->person->removePreference('favorites', [
            'name' => 'Updates',
            'url' => 'https://worldwideweb.com/cp/updater/statamic'
        ]);

        $expected = [
            'collection' => [
                'columns' => [
                    'title'
                ],
            ],
            'favorites' => [
                [
                    'name' => 'Blog',
                    'url' => 'https://worldwideweb.com/cp/collections/blog'
                ]
            ]
        ];

        $this->assertEquals($expected, $this->person->preferences());
    }

    /** @test */
    function it_can_get_a_single_preference()
    {
        $this->person->preferences([
            'collection' => [
                'filters' => [
                    'published'
                ]
            ]
        ]);

        $this->assertEquals(['filters' => ['published']], $this->person->getPreference('collection'));
        $this->assertEquals(['published'], $this->person->getPreference('collection.filters'));
        $this->assertEquals(null, $this->person->getPreference('language'));
    }

    /** @test */
    function it_can_check_if_a_single_preference_exists()
    {
        $this->person->preferences([
            'collection' => [
                'filters' => [
                    'published'
                ]
            ]
        ]);

        $this->assertTrue($this->person->hasPreference('collection'));
        $this->assertTrue($this->person->hasPreference('collection.filters'));
        $this->assertFalse($this->person->hasPreference('language'));
    }

    /** @test */
    function it_can_modify_a_preference()
    {
        $this->person->setPreference('favorite', 'pizza');

        $this->person->modifyPreference('favorite', function ($value) {
            return strtoupper($value);
        });

        $this->assertEquals('PIZZA', $this->person->getPreference('favorite'));
    }

    /** @test */
    function it_can_append_to_a_preference()
    {
        $this->person->appendPreferences('favorite', ['pizza', 'lasagna']);
        $this->person->appendPreference('favorite', 'rigatoni');

        $expected = [
            'pizza',
            'lasagna',
            'rigatoni'
        ];

        $this->assertEquals($expected, $this->person->getPreference('favorite'));
    }

    /** @test */
    function it_can_cleanup_a_preference()
    {
        $this->person->preferences([
            'collection' => [
                'example-one' => [
                    'deeply' => [
                        'nested' => [
                            'empty-array' => []
                        ]
                    ]
                ],
                'example-two' => [
                    'deeply' => [
                        'nested' => [
                            'empty-string' => ''
                        ]
                    ]
                ],
                'example-three' => [
                    'keep-example-three',
                    'deeply' => [
                        'nested' => [
                            'null' => null
                        ]
                    ]
                ],
                'example-four' => [
                    'integer' => 0
                ],
                'example-five' => [
                    'false' => false,
                ],
                'columns' => [
                    'title'
                ]
            ],
            'filled-top-level' => false,
            'empty-top-level' => []
        ]);

        $expected = [
            'collection' => [
                'example-three' => [
                    'keep-example-three',
                ],
                'example-four' => [
                    'integer' => 0
                ],
                'example-five' => [
                    'false' => false
                ],
                'columns' => [
                    'title'
                ],
            ],
            'filled-top-level' => false
        ];

        $this->person
            ->cleanupPreference('collection.example-one.deeply.nested.empty-array')
            ->cleanupPreference('collection.example-two.deeply.nested.empty-string')
            ->cleanupPreference('collection.example-three.deeply.nested.null')
            ->cleanupPreference('collection.example-four.integer')
            ->cleanupPreference('collection.example-five.false')
            ->cleanupPreference('some.non.existent.pref.that.was.maybe.previously.deleted')
            ->cleanupPreference('filled-top-level')
            ->cleanupPreference('empty-top-level');

        $this->assertEquals($expected, $this->person->preferences());
    }
}

class Person
{
    use HasPreferences;
}
