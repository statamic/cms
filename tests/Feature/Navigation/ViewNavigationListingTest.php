<?php

namespace Tests\Feature\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewNavigationListingTest extends TestCase
{
    use MocksStructures;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_a_list_of_nav_structures()
    {
        Facades\Nav::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createNav('foo'),
            'bar' => $structureB = $this->createNav('bar'),
        ]));

        $user = Facades\User::make()->makeSuper()->save();

        $this
            ->actingAs($user)
            ->visitIndex()
            ->assertSuccessful()
            ->assertViewHas('navs', function ($navs) {
                return $navs->map->id->all() === ['foo', 'bar'];
            })
            ->assertDontSee('no-results');
    }

    #[Test]
    public function it_shows_no_results_when_there_are_no_structures()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->visitIndex()
            ->assertSuccessful()
            ->assertSee('Get started by creating your first navigation');
    }

    #[Test]
    public function it_filters_out_structures_the_user_cannot_access()
    {
        Facades\Nav::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createNav('foo'),
            'bar' => $this->createNav('bar'),
        ]));
        $this->setTestRoles(['test' => ['access cp', 'view bar nav']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->visitIndex()
            ->assertSuccessful()
            ->assertViewHas('navs', function ($navs) {
                return $navs->map->id->all() === ['bar'];
            })
            ->assertDontSee('no-results');
    }

    #[Test]
    public function it_doesnt_filter_out_structures_if_they_have_permission_to_configure()
    {
        Facades\Nav::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createNav('foo'),
            'bar' => $this->createNav('bar'),
        ]));
        $this->setTestRoles(['test' => ['access cp', 'configure navs', 'view bar nav']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->visitIndex()
            ->assertSuccessful()
            ->assertViewHas('navs', function ($navs) {
                return $navs->map->id->all() === ['foo', 'bar'];
            })
            ->assertDontSee('no-results');
    }

    #[Test]
    public function it_denies_access_when_there_are_no_permitted_structures()
    {
        Facades\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createNav('foo'),
            'bar' => $this->createNav('bar'),
        ]));

        $this->setTestRoles(['test' => ['access cp']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->visitIndex()
            ->assertRedirect('/cp/original');
    }

    #[Test]
    public function create_structure_button_is_visible_with_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->visitIndex()
            ->assertSee('Create a Navigation');
    }

    #[Test]
    public function create_structure_button_is_not_visible_without_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = Facades\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->visitIndex()
            ->assertDontSee('Create Navigation');
    }

    #[Test]
    public function delete_button_is_visible_with_permission_to_configure()
    {
        Facades\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createNav('foo'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->visitIndex()
            ->assertSee('Delete');
    }

    #[Test]
    public function delete_button_is_not_visible_without_permission_to_configure()
    {
        $this->markTestIncomplete();

        Facades\Nav::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createNav('foo'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'view foo nav']]);
        $user = Facades\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->visitIndex()
            ->assertDontSee('Delete');
    }

    private function setTestRoles($roles)
    {
        $roles = collect($roles)->map(function ($permissions, $handle) {
            return Facades\Role::make()
                ->handle($handle)
                ->addPermission($permissions);
        });

        $fake = new class($roles) extends \Statamic\Auth\File\RoleRepository
        {
            protected $roles;

            public function __construct($roles)
            {
                $this->roles = $roles;
            }

            public function all(): \Illuminate\Support\Collection
            {
                return $this->roles;
            }
        };

        app()->instance(\Statamic\Contracts\Auth\RoleRepository::class, $fake);
        Facades\Role::swap($fake);
    }

    private function visitIndex()
    {
        return $this->get(route('statamic.cp.navigation.index'));
    }
}
