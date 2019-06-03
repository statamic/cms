<?php

namespace Tests\Feature\Structures;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Statamic\Auth\User;
use Statamic\Data\Structures\Structure;
use Tests\PreventSavingStacheItemsToDisk;

class ViewStructureListingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_shows_a_list_of_structures()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));

        $user = API\User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', function ($structures) {
                return $structures->map->id->all() === ['foo', 'bar'];
            })
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_shows_no_results_when_there_are_no_structures()
    {
        $user = API\User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', collect([]))
            ->assertSee('no-results');
    }

    /** @test */
    function it_filters_out_structures_the_user_cannot_access()
    {
        $this->withoutExceptionHandling();
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));
        $this->setTestRoles(['test' => ['access cp', 'view bar structure']]);
        $user = API\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', function ($structures) {
                return $structures->map->id->all() === ['bar'];
            })
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_doesnt_filter_out_structures_if_they_have_permission_to_configure()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));
        $this->setTestRoles(['test' => ['access cp', 'configure structures', 'view bar structure']]);
        $user = API\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSuccessful()
            ->assertViewHas('structures', function ($structures) {
                return $structures->map->id->all() === ['foo', 'bar'];
            })
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_denies_access_when_there_are_no_permitted_structures()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $structureA = $this->createStructure('foo'),
            'bar' => $structureB = $this->createStructure('bar')
        ]));

        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test')->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertRedirect('/cp/original');
    }

    /** @test */
    function create_structure_button_is_visible_with_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure structures']]);
        $user = API\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSee('Create Structure');
    }

    /** @test */
    function create_structure_button_is_not_visible_without_permission_to_configure()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertDontSee('Create Structure');
    }

    /** @test */
    function delete_button_is_visible_with_permission_to_configure()
    {
        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createStructure('foo'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'configure structures']]);
        $user = API\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertSee('Delete');
    }

    /** @test */
    function delete_button_is_not_visible_without_permission_to_configure()
    {
        $this->markTestIncomplete();

        API\Structure::shouldReceive('all')->andReturn(collect([
            'foo' => $this->createStructure('foo'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'view foo structure']]);
        $user = API\User::make()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.index'))
            ->assertDontSee('Delete');
    }

    private function createStructure($handle)
    {
        return tap(Mockery::mock(Structure::class), function ($s) use ($handle) {
            $s->shouldReceive('title')->andReturn($handle);
            $s->shouldReceive('handle')->andReturn($handle);
            $s->shouldReceive('uris')->andReturn(collect());
            $s->shouldReceive('flattenedPages')->andReturn(collect());
        });
    }

    private function setTestRoles($roles)
    {
        $roles = collect($roles)->map(function ($permissions, $handle) {
            return API\Role::make()
                ->handle($handle)
                ->addPermission($permissions);
        });

        $fake = new class($roles) extends \Statamic\Auth\RoleRepository {
            protected $roles;
            public function __construct($roles) {
                $this->roles = $roles;
            }
            public function all(): \Illuminate\Support\Collection {
                return $this->roles;
            }
        };

        app()->instance(\Statamic\Contracts\Auth\RoleRepository::class, $fake);
        API\Role::swap($fake);
    }
}
