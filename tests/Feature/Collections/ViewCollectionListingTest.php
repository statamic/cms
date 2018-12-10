<?php

namespace Tests\Feature\Collections;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Auth\User;
use Statamic\Data\Entries\Collection;

class ViewCollectionListingTest extends TestCase
{
    use FakesRoles;

    public function setUp()
    {
        parent::setUp();
        $this->markTestIncomplete(); // TODO. It's changed since we moved to a vue component.
    }

    /** @test */
    function it_shows_a_list_of_collections()
    {
        API\Collection::shouldReceive('all')->andReturn(collect([
            'foo' => $collectionA = $this->createCollection('foo'),
            'bar' => $collectionB = $this->createCollection('bar')
        ]));

        $user = User::make()->makeSuper();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', collect([
                'foo' => $collectionA,
                'bar' => $collectionB
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_shows_no_results_when_there_are_no_collections()
    {
        $user = User::make()->makeSuper();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', collect([]))
            ->assertSee('no-results');
    }

    /** @test */
    function it_filters_out_collections_the_user_cannot_access()
    {
        API\Collection::shouldReceive('all')->andReturn(collect([
            'foo' => $collectionA = $this->createCollection('foo'),
            'bar' => $collectionB = $this->createCollection('bar')
        ]));
        $this->setTestRoles(['test' => ['access cp', 'view bar collection']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', collect([
                'bar' => $collectionB
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_doesnt_filter_out_collections_if_they_have_permission_to_configure()
    {
        API\Collection::shouldReceive('all')->andReturn(collect([
            'foo' => $collectionA = $this->createCollection('foo'),
            'bar' => $collectionB = $this->createCollection('bar')
        ]));
        $this->setTestRoles(['test' => ['access cp', 'configure collections', 'view bar collection']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', collect([
                'foo' => $collectionA,
                'bar' => $collectionB
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_denies_access_when_there_are_no_permitted_collections()
    {
        API\Collection::shouldReceive('all')->andReturn(collect([
            'foo' => $collectionA = $this->createCollection('foo'),
            'bar' => $collectionB = $this->createCollection('bar')
        ]));

        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertRedirect('/cp/original');
    }

    /** @test */
    function create_collection_button_is_visible_with_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSee('Create Collection');
    }

    /** @test */
    function create_collection_button_is_not_visible_without_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertDontSee('Create Collection');
    }

    /** @test */
    function delete_button_is_visible_with_permission_to_configure()
    {
        API\Collection::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createCollection('foo'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSee('Delete');
    }

    /** @test */
    function delete_button_is_not_visible_without_permission_to_configure()
    {
        API\Collection::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createCollection('foo'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'view foo collection']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertDontSee('Delete');
    }

    private function createCollection($handle)
    {
        return tap(new Collection)->path($handle);
    }
}
