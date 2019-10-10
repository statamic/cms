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

    /** @test */
    function it_shows_the_edit_form_if_user_has_edit_permission()
    {
        $structure = $this->createStructure('foo');
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
        $structure = $this->createStructure('foo');
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

    private function createStructure($handle)
    {
        return tap(Mockery::mock(Structure::class), function ($s) use ($handle) {
            $s->shouldReceive('in')->andReturn($this->createStructureTree($handle));
            $s->shouldReceive('title')->andReturn($handle);
            $s->shouldReceive('handle')->andReturn($handle);
            $s->shouldReceive('uris')->andReturn(collect());
            $s->shouldReceive('collection')->andReturnFalse();
            $s->shouldReceive('collections')->andReturn(collect());
            $s->shouldReceive('expectsRoot')->andReturnTrue();
            $s->shouldReceive('flattenedPages')->andReturn(collect());
        });
    }

    private function createStructureTree($handle)
    {
        return tap(Mockery::mock(Tree::class), function ($s) use ($handle) {
            $s->shouldReceive('editUrl')->andReturn('/tree-edit-url');
            $s->shouldReceive('route')->andReturn('/route');
        });
    }
}
