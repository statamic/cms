<?php

namespace Tests\Feature\Structures;

use Mockery;
use Statamic\Facades;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Auth\User;
use Statamic\Structures\Structure;
use Tests\PreventSavingStacheItemsToDisk;

class EditStructureTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;
    use MocksStructures;

    /** @test */
    function it_shows_the_edit_form_if_user_has_edit_permission()
    {
        $structure = $this->createNavStructure('foo');
        Facades\Structure::shouldReceive('all')->andReturn(collect([$structure]));
        Facades\Structure::shouldReceive('find')->andReturn($structure);

        $this->setTestRoles(['test' => ['access cp', 'edit foo structure']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.edit', $structure->handle()))
            ->assertSuccessful()
            ->assertViewHas('structure', $structure);
    }

    /** @test */
    function it_denies_access_if_user_doesnt_have_edit_permission()
    {
        $structure = $this->createNavStructure('foo');
        Facades\Structure::shouldReceive('all')->andReturn(collect([$structure]));
        Facades\Structure::shouldReceive('find')->andReturn($structure);

        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(route('statamic.cp.structures.edit', $structure->handle()))
            ->assertRedirect('/cp/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function attempting_to_edit_a_collection_based_structure_should_404()
    {
        $structure = $this->createCollectionStructure('foo');
        Facades\Structure::shouldReceive('all')->andReturn(collect([$structure]));
        Facades\Structure::shouldReceive('find')->andReturn($structure);

        $this->setTestRoles(['test' => ['access cp', 'edit foo structure']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.cp.structures.edit', $structure->handle()))
            ->assertNotFound();
    }
}
