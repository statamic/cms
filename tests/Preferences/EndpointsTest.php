<?php

namespace Tests\Preferences;

use Tests\TestCase;
use Statamic\API\User;
use Statamic\API\Preference;

class EndpointsTest extends TestCase
{
    public function tearDown()
    {
        // TODO: Re-implement delete() on user.
        User::all()->each(function ($user) {
            \File::delete($user->path());
        });

        parent::tearDown();
    }

    /** @test */
    function it_can_set_a_preference()
    {
        $user = User::make()->makeSuper()->save();

        $expected = [
            'favorites' => [
                'foods' => [
                    'pizza',
                    'lasagna'
                ]
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post(cp_route('preferences.store'), [
                'key' => 'favorites.foods',
                'value' => [
                    'pizza',
                    'lasagna'
                ]
            ])
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }

    /** @test */
    function it_can_append_a_preference()
    {
        $user = User::make()
            ->makeSuper()
            ->setPreference('favorites.foods', ['pizza', 'lasagna'])
            ->save();

        $expected = [
            'favorites' => [
                'foods' => [
                    'pizza',
                    'lasagna',
                    'spaghetti'
                ]
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->post(cp_route('preferences.store'), [
                'key' => 'favorites.foods',
                'value' => 'spaghetti',
                'append' => true
            ])
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }

    /** @test */
    function it_can_remove_a_preference()
    {
        $user = User::make()
            ->makeSuper()
            ->setPreferences([
                'food' => 'pizza',
                'color' => 'red'
            ])
            ->save();

        $expected = [
            'color' => 'red'
        ];

        $response = $this
            ->actingAs($user)
            ->delete(cp_route('preferences.destroy', 'food'))
            ->assertExactJson($expected);

        $this->assertEquals($expected, Preference::all());
    }
}
