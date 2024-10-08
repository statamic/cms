<?php

namespace Tests\Preferences;

use PHPUnit\Framework\Attributes\Test;

trait HasPreferencesTests
{
    #[Test]
    public function it_can_get_and_set_array_of_preferences()
    {
        $preferences = ['language' => 'english'];

        $user = $this->makeUser();

        $this->assertEquals([], $user->preferences());

        $user->preferences($preferences);

        $this->assertEquals($preferences, $user->preferences());
    }

    #[Test]
    public function it_can_set_array_of_preferences()
    {
        $user = $this->makeUser();

        $user->preferences([
            'language' => 'english',
            'color' => 'red',
        ]);

        $user->setPreferences([
            'language' => 'french',
            'music' => 'metal',
        ]);

        $expected = [
            'language' => 'french',
            'music' => 'metal',
        ];

        $this->assertEquals($expected, $user->preferences());
    }

    #[Test]
    public function it_can_merge_array_of_preferences()
    {
        $user = $this->makeUser();

        $user->preferences([
            'language' => 'english',
            'color' => 'red',
        ]);

        $user->mergePreferences([
            'language' => 'french',
            'music' => 'metal',
        ]);

        $expected = [
            'language' => 'french',
            'color' => 'red',
            'music' => 'metal',
        ];

        $this->assertEquals($expected, $user->preferences());
    }

    #[Test]
    public function it_can_set_a_single_preference()
    {
        $user = $this->makeUser();

        $user->setPreference('collection.columns', ['date', 'title']);

        $expected = [
            'collection' => [
                'columns' => [
                    'date',
                    'title',
                ],
            ],
        ];

        $this->assertEquals($expected, $user->preferences());
    }

    #[Test]
    public function it_can_remove_a_single_preference()
    {
        $user = $this->makeUser();

        $user->preferences([
            'collection' => [
                'columns' => [
                    'date',
                    'title',
                ],
                'filters' => [
                    'published',
                ],
            ],
        ]);

        $user->removePreference('collection.columns');

        $expected = [
            'collection' => [
                'filters' => [
                    'published',
                ],
            ],
        ];

        $this->assertEquals($expected, $user->preferences());
    }

    #[Test]
    public function it_can_remove_a_single_preference_array_value()
    {
        $user = $this->makeUser();

        $user->preferences([
            'collection' => [
                'columns' => [
                    'date',
                    'title',
                    'slug',
                ],
            ],
            'favorites' => [
                [
                    'name' => 'Updates',
                    'url' => 'https://worldwideweb.com/cp/updater/statamic',
                ],
                [
                    'name' => 'Blog',
                    'url' => 'https://worldwideweb.com/cp/collections/blog',
                ],
            ],
        ]);

        $user->removePreference('collection.columns', 'date');
        $user->removePreference('collection.columns', 'slug');
        $user->removePreference('favorites', [
            'name' => 'Updates',
            'url' => 'https://worldwideweb.com/cp/updater/statamic',
        ]);

        $expected = [
            'collection' => [
                'columns' => [
                    'title',
                ],
            ],
            'favorites' => [
                [
                    'name' => 'Blog',
                    'url' => 'https://worldwideweb.com/cp/collections/blog',
                ],
            ],
        ];

        $this->assertEquals($expected, $user->preferences());
    }

    #[Test]
    public function it_cleans_up_by_default_after_removing()
    {
        $user = $this->makeUser();

        $user->preferences([
            'favorites' => [
                [
                    'name' => 'Updates',
                    'url' => 'https://worldwideweb.com/cp/updater/statamic',
                ],
            ],
        ]);

        $user->removePreference('favorites', [
            'name' => 'Updates',
            'url' => 'https://worldwideweb.com/cp/updater/statamic',
        ]);

        $this->assertEquals([], $user->preferences());
    }

    #[Test]
    public function it_can_remove_with_cleanup_disabled()
    {
        $user = $this->makeUser();

        $user->preferences([
            'favorites' => [
                [
                    'name' => 'Updates',
                    'url' => 'https://worldwideweb.com/cp/updater/statamic',
                ],
            ],
        ]);

        $user->removePreference('favorites', [
            'name' => 'Updates',
            'url' => 'https://worldwideweb.com/cp/updater/statamic',
        ], false);

        $expected = [
            'favorites' => [],
        ];

        $this->assertEquals($expected, $user->preferences());
    }

    #[Test]
    public function it_can_get_a_single_preference()
    {
        $user = $this->makeUser();

        $user->preferences([
            'collection' => [
                'filters' => [
                    'published',
                ],
            ],
        ]);

        $this->assertEquals(['filters' => ['published']], $user->getPreference('collection'));
        $this->assertEquals(['published'], $user->getPreference('collection.filters'));
        $this->assertEquals(null, $user->getPreference('language'));
    }

    #[Test]
    public function it_can_check_if_a_single_preference_exists()
    {
        $user = $this->makeUser();

        $user->preferences([
            'collection' => [
                'filters' => [
                    'published',
                ],
            ],
        ]);

        $this->assertTrue($user->hasPreference('collection'));
        $this->assertTrue($user->hasPreference('collection.filters'));
        $this->assertFalse($user->hasPreference('language'));
    }

    #[Test]
    public function it_can_modify_a_preference()
    {
        $user = $this->makeUser();

        $user->setPreference('favorite', 'pizza');

        $user->modifyPreference('favorite', function ($value) {
            return strtoupper($value);
        });

        $this->assertEquals('PIZZA', $user->getPreference('favorite'));
    }

    #[Test]
    public function it_can_append_to_a_preference()
    {
        $user = $this->makeUser();

        $user->appendPreferences('favorite', ['pizza', 'lasagna']);
        $user->appendPreference('favorite', 'rigatoni');

        $expected = [
            'pizza',
            'lasagna',
            'rigatoni',
        ];

        $this->assertEquals($expected, $user->getPreference('favorite'));
    }

    #[Test]
    public function it_can_cleanup_a_preference()
    {
        $user = $this->makeUser();

        $user->preferences([
            'collection' => [
                'example-one' => [
                    'deeply' => [
                        'nested' => [
                            'empty-array' => [],
                        ],
                    ],
                ],
                'example-two' => [
                    'deeply' => [
                        'nested' => [
                            'empty-string' => '',
                        ],
                    ],
                ],
                'example-three' => [
                    'keep-example-three',
                    'deeply' => [
                        'nested' => [
                            'null' => null,
                        ],
                    ],
                ],
                'example-four' => [
                    'integer' => 0,
                ],
                'example-five' => [
                    'false' => false,
                ],
                'columns' => [
                    'title',
                ],
            ],
            'filled-top-level' => false,
            'empty-top-level' => [],
        ]);

        $expected = [
            'collection' => [
                'example-three' => [
                    'keep-example-three',
                ],
                'example-four' => [
                    'integer' => 0,
                ],
                'example-five' => [
                    'false' => false,
                ],
                'columns' => [
                    'title',
                ],
            ],
            'filled-top-level' => false,
        ];

        $user
            ->cleanupPreference('collection.example-one.deeply.nested.empty-array')
            ->cleanupPreference('collection.example-two.deeply.nested.empty-string')
            ->cleanupPreference('collection.example-three.deeply.nested.null')
            ->cleanupPreference('collection.example-four.integer')
            ->cleanupPreference('collection.example-five.false')
            ->cleanupPreference('some.non.existent.pref.that.was.maybe.previously.deleted')
            ->cleanupPreference('filled-top-level')
            ->cleanupPreference('empty-top-level');

        $this->assertEquals($expected, $user->preferences());
    }
}
