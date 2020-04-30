<?php

namespace Tests\Preferences;

use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\TestCase;

class PrecedenceTest extends TestCase
{
    use FakesRoles;

    /** @test */
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

    /** @test */
    public function it_can_fallback_when_preference_doesnt_exist()
    {
        $this->actingAs(User::make()->makeSuper());

        $this->assertEquals('saints', Preference::get('nfl.teams.favorite', 'saints'));
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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
}
