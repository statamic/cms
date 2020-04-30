<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Auth\User;
use Statamic\Entries\Collection;
use Statamic\Facades;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewCollectionListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_shows_a_list_of_collections()
    {
        $collectionA = $this->createCollection('foo');
        $collectionB = $this->createCollection('bar');
        EntryFactory::id('1')->collection($collectionB)->create();

        $user = tap(User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', collect([
                [
                    'id' => 'foo',
                    'title' => 'Foo',
                    'entries' => 0,
                    'edit_url' => 'http://localhost/cp/collections/foo/edit',
                    'delete_url' => 'http://localhost/cp/collections/foo',
                    'entries_url' => 'http://localhost/cp/collections/foo',
                    'scaffold_url' => 'http://localhost/cp/collections/foo/scaffold',
                    'deleteable' => true,
                ],
                [
                    'id' => 'bar',
                    'title' => 'Bar',
                    'entries' => 1,
                    'edit_url' => 'http://localhost/cp/collections/bar/edit',
                    'delete_url' => 'http://localhost/cp/collections/bar',
                    'entries_url' => 'http://localhost/cp/collections/bar',
                    'scaffold_url' => 'http://localhost/cp/collections/bar/scaffold',
                    'deleteable' => true,
                ],
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_shows_no_results_when_there_are_no_collections()
    {
        $user = tap(User::make()->makeSuper())->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', collect([]))
            ->assertSee('no-results');
    }

    /** @test */
    public function it_filters_out_collections_the_user_cannot_access()
    {
        $collectionA = $this->createCollection('foo');
        $collectionB = $this->createCollection('bar');
        $this->setTestRoles(['test' => ['access cp', 'view bar entries']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', function ($collections) {
                return count($collections) === 1 && $collections[0]['id'] === 'bar';
            })
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_doesnt_filter_out_collections_if_they_have_permission_to_configure()
    {
        $collectionA = $this->createCollection('foo');
        $collectionB = $this->createCollection('bar');
        $this->setTestRoles(['test' => ['access cp', 'configure collections', 'view bar entries']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSuccessful()
            ->assertViewHas('collections', function ($collections) {
                return $collections->map->id->all() === ['foo', 'bar'];
            })
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_denies_access_when_there_are_no_permitted_collections()
    {
        $collectionA = $this->createCollection('foo');
        $collectionB = $this->createCollection('bar');
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertRedirect('/cp/original');
    }

    /** @test */
    public function create_collection_button_is_visible_with_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertSee('Create Collection');
    }

    /** @test */
    public function create_collection_button_is_not_visible_without_permission_to_configure()
    {
        $collectionA = $this->createCollection('foo');
        $this->setTestRoles(['test' => ['access cp', 'view foo entries']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.index'))
            ->assertOk()
            ->assertDontSee('Create Collection');
    }

    private function createCollection($handle)
    {
        return tap((new Collection)->handle($handle))->save();
    }
}
