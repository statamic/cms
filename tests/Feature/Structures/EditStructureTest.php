<?php

namespace Tests\Feature\Structures;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Statamic\Data\Users\User;
use Statamic\Data\Structures\Structure;

class EditStructureTest extends TestCase
{
    /** @test */
    function it_shows_the_edit_form_if_user_has_edit_permission()
    {
        $structure = $this->createStructure('foo');
        API\Structure::shouldReceive('all')->andReturn(collect([$structure]));
        API\Structure::shouldReceive('find')->andReturn($structure);

        $this->setTestRoles(['test' => ['access cp', 'edit foo structure']]);
        $user = API\User::create()->get()->assignRole('test');

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.edit', $structure->handle()))
            ->assertSuccessful()
            ->assertViewHas('structure', $structure);
    }

    /** @test */
    function it_denies_access_if_user_doesnt_have_edit_permission()
    {
        $structure = $this->createStructure('foo');
        API\Structure::shouldReceive('all')->andReturn(collect([$structure]));
        API\Structure::shouldReceive('find')->andReturn($structure);

        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create()->get()->assignRole('test');

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(route('statamic.cp.structures.edit', $structure->handle()))
            ->assertRedirect('/cp/original')
            ->assertSessionHasErrors();
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
            return app(\Statamic\Contracts\Permissions\Role::class)
                ->handle($handle)
                ->addPermission($permissions);
        });

        $fake = new class($roles) extends \Statamic\Permissions\RoleRepository {
            protected $roles;
            public function __construct($roles) {
                $this->roles = $roles;
            }
            public function all(): \Illuminate\Support\Collection {
                return $this->roles;
            }
        };

        app()->instance(\Statamic\Contracts\Permissions\RoleRepository::class, $fake);
    }
}
