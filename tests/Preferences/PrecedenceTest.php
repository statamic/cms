<?php

namespace Tests\Preferences;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\TestCase;

class PrecedenceTest extends TestCase
{
    use FakesRoles;

    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->cleanup();
    }

    public function tearDown(): void
    {
        $this->cleanup();

        parent::tearDown();
    }

    #[Test]
    public function it_can_get_user_preferences()
    {
        $preferences = [
            'site' => 'english',
            'columns' => [
                'collections' => [
                    'blog' => [
                        'title',
                        'slug',
                    ],
                ],
            ],
        ];

        $this->actingAs(User::make()->preferences($preferences));

        $this->assertEquals('english', Preference::get('site'));
        $this->assertEquals(['title', 'slug'], Preference::get('columns.collections.blog'));
        $this->assertEquals($preferences, Preference::all());
    }

    #[Test]
    public function it_can_fallback_when_preference_doesnt_exist()
    {
        $this->actingAs(User::make()->makeSuper());

        $this->assertEquals('saints', Preference::get('nfl.teams.favorite', 'saints'));
    }

    #[Test]
    public function it_can_get_user_role_preferences()
    {
        $preferences = [
            'site' => 'english',
            'columns' => [
                'collections' => [
                    'blog' => [
                        'title',
                        'slug',
                    ],
                ],
            ],
        ];

        $this->setTestRoles(['author' => Role::make()->permissions('super')->preferences($preferences)]);
        $this->actingAs(User::make()->assignRole('author'));

        $this->assertEquals('english', Preference::get('site'));
        $this->assertEquals(['title', 'slug'], Preference::get('columns.collections.blog'));
        $this->assertEquals($preferences, Preference::all());
    }

    #[Test]
    public function it_gives_precedence_to_role_order_assigned_on_user()
    {
        $this->setTestRoles([
            'author' => Role::make()->permissions('super')->preferences(['alpha' => 'foo', 'beta' => 'beta']),
            'pleb' => Role::make()->permissions('super')->preferences(['alpha' => 'bar', 'charlie' => 'charlie']),
        ]);

        $this->actingAs(User::make()->explicitRoles(['author', 'pleb']));

        $expected = [
            'alpha' => 'foo', // This should be `foo`, because the `author` role is set first
            'beta' => 'beta',
            'charlie' => 'charlie',
        ];

        $this->assertEquals($expected, Preference::all());
    }

    #[Test]
    public function it_can_get_default_preferences()
    {
        $this->actingAs(User::make()->assignRole('author'));

        $this->assertEquals([], Preference::all());

        Preference::default()->set($preferences = [
            'site' => 'english',
            'columns' => [
                'collections' => [
                    'blog' => [
                        'title',
                        'slug',
                    ],
                ],
            ],
        ])->save();

        $this->assertEquals('english', Preference::get('site'));
        $this->assertEquals(['title', 'slug'], Preference::get('columns.collections.blog'));
        $this->assertEquals($preferences, Preference::all());
    }

    #[Test]
    public function it_gives_precedence_to_higher_roles_over_lower_roles_as_defined_on_user()
    {
        $this->setTestRoles([
            'bear' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'meat',
                    'hibernates' => true,
                ],
            ]),
            'rabbit' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'lettuce',
                    'hops' => true,
                ],
            ]),
        ]);

        $this->actingAs(User::make()->assignRole('rabbit')->assignRole('bear'));

        $this->assertEquals('lettuce', Preference::get('actions.eats'));
        $this->assertTrue(Preference::get('actions.hibernates'));
        $this->assertTrue(Preference::get('actions.hops'));
    }

    #[Test]
    public function it_gives_precedence_to_user_preferences_over_role_preferences()
    {
        $this->setTestRoles([
            'bear' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'meat',
                    'hibernates' => true,
                ],
            ]),
            'rabbit' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'lettuce',
                    'hops' => true,
                ],
            ]),
        ]);

        $this->actingAs(User::make()->assignRole('rabbit')->assignRole('bear')->preferences([
            'actions' => [
                'hibernates' => false,
            ],
        ]));

        $this->assertEquals('lettuce', Preference::get('actions.eats'));
        $this->assertFalse(Preference::get('actions.hibernates'));
        $this->assertTrue(Preference::get('actions.hops'));
    }

    #[Test]
    public function it_gives_precedence_to_user_and_role_preferences_over_default_preferences()
    {
        $this->actingAs(User::make()->assignRole('rabbit')->assignRole('bear')->preferences([
            'actions' => [
                'hibernates' => false,
            ],
            'deeply' => [
                'nested' => [
                    'user' => true,
                ],
            ],
        ]));

        $this->setTestRoles([
            'bear' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'meat',
                    'hibernates' => true,
                ],
                'deeply' => [
                    'nested' => [
                        'bear' => true,
                    ],
                ],
            ]),
            'rabbit' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'lettuce',
                    'hops' => true,
                ],
                'deeply' => [
                    'nested' => [
                        'rabbit' => true,
                    ],
                ],
            ]),
        ]);

        Preference::default()->set([
            'site' => 'english',
            'actions' => [
                'eats' => 'pizza',
                'walks' => true,
            ],
            'deeply' => [
                'nested' => [
                    'default' => true,
                ],
            ],
        ])->save();

        $this->assertEquals('english', Preference::get('site'));
        $this->assertEquals('lettuce', Preference::get('actions.eats'));
        $this->assertFalse(Preference::get('actions.hibernates'));
        $this->assertTrue(Preference::get('actions.hops'));
        $this->assertTrue(Preference::get('actions.walks'));
        $this->assertTrue(Preference::get('deeply.nested.user'));
        $this->assertTrue(Preference::get('deeply.nested.bear'));
        $this->assertTrue(Preference::get('deeply.nested.rabbit'));
        $this->assertTrue(Preference::get('deeply.nested.default'));
    }

    #[Test]
    public function it_merges_preferences_at_every_level_unless_otherwise_configured()
    {
        $this->actingAs(User::make()->assignRole('rabbit')->assignRole('bear')->preferences([
            'actions' => [
                'hibernates' => false,
            ],
            'deeply' => [
                'nested' => [
                    'user' => true,
                ],
            ],
        ]));

        $this->setTestRoles([
            'rabbit' => Role::make()->permissions('super')->preferences([
                'actions' => [
                    'eats' => 'lettuce',
                    'hops' => true,
                ],
                'deeply' => [
                    'nested' => [
                        'role' => true,
                    ],
                ],
            ]),
        ]);

        Preference::default()->set([
            'site' => 'english',
            'actions' => [
                'eats' => 'pizza',
                'walks' => true,
            ],
            'deeply' => [
                'nested' => [
                    'default' => true,
                ],
            ],
        ])->save();

        Preference::preventMergingChildren('actions');
        Preference::preventMergingChildren('deeply.nested');

        $this->assertEquals('english', Preference::get('site'));
        $this->assertFalse(Preference::get('actions.hibernates'));
        $this->assertNull(Preference::get('actions.eats'));
        $this->assertNull(Preference::get('actions.hops'));
        $this->assertNull(Preference::get('actions.walks'));
        $this->assertTrue(Preference::get('deeply.nested.user'));
        $this->assertNull(Preference::get('deeply.nested.role'));
        $this->assertNull(Preference::get('deeply.nested.default'));
    }

    #[Test]
    public function it_overrides_preferences_at_role_level_using_an_empty_array()
    {
        $this->actingAs(User::make()->assignRole('rabbit')->assignRole('bear'));

        $this->setTestRoles([
            'rabbit' => Role::make()->permissions('super')->preferences([
                'actions' => [],
                'deeply' => [
                    'nested' => [],
                ],
            ]),
        ]);

        Preference::default()->set([
            'site' => 'english',
            'actions' => [
                'eats' => 'pizza',
                'walks' => true,
            ],
            'deeply' => [
                'nested' => [
                    'default' => true,
                ],
            ],
        ])->save();

        Preference::preventMergingChildren('actions');
        Preference::preventMergingChildren('deeply.nested');

        $this->assertEquals('english', Preference::get('site'));
        $this->assertEquals([], Preference::get('actions'));
        $this->assertNull(Preference::get('actions.eats'));
        $this->assertNull(Preference::get('actions.hops'));
        $this->assertNull(Preference::get('actions.walks'));
        $this->assertEquals([], Preference::get('deeply.nested'));
        $this->assertNull(Preference::get('deeply.nested.user'));
        $this->assertNull(Preference::get('deeply.nested.role'));
        $this->assertNull(Preference::get('deeply.nested.default'));
    }

    #[Test]
    public function it_overrides_preferences_at_user_level_using_an_empty_array()
    {
        $this->actingAs(User::make()->preferences([
            'actions' => [],
            'deeply' => [
                'nested' => [],
            ],
        ]));

        Preference::default()->set([
            'site' => 'english',
            'actions' => [
                'eats' => 'pizza',
                'walks' => true,
            ],
            'deeply' => [
                'nested' => [
                    'default' => true,
                ],
            ],
        ])->save();

        Preference::preventMergingChildren('actions');
        Preference::preventMergingChildren('deeply.nested');

        $this->assertEquals('english', Preference::get('site'));
        $this->assertEquals([], Preference::get('actions'));
        $this->assertNull(Preference::get('actions.eats'));
        $this->assertNull(Preference::get('actions.hops'));
        $this->assertNull(Preference::get('actions.walks'));
        $this->assertEquals([], Preference::get('deeply.nested'));
        $this->assertNull(Preference::get('deeply.nested.user'));
        $this->assertNull(Preference::get('deeply.nested.role'));
        $this->assertNull(Preference::get('deeply.nested.default'));
    }

    private function cleanup()
    {
        if ($this->files->exists($path = resource_path('preferences.yaml'))) {
            $this->files->delete($path);
        }
    }
}
