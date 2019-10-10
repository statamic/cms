<?php

namespace Tests\Feature\Blueprints;

use Mockery;
use Statamic\Facades;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Auth\User;
use Statamic\Fields\Blueprint;
use Statamic\Entries\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class ViewBlueprintListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_shows_a_list_of_fieldsets()
    {
        // When the CP header loads the avatar it reaches for the user blueprint.
        Facades\Blueprint::shouldReceive('find')->with('user')->andReturn(new Blueprint);

        Facades\Blueprint::shouldReceive('all')->andReturn(collect([
            'foo' => $blueprintA = $this->createBlueprint('foo'),
            'bar' => $blueprintB = $this->createBlueprint('bar')
        ]));

        $user = Facades\User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('blueprints.index'))
            ->assertSuccessful()
            ->assertViewHas('blueprints', collect([
                [
                    'id' => 'foo',
                    'handle' => 'foo',
                    'title' => 'Foo',
                    'sections' => 0,
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/blueprints/foo/edit'
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'sections' => 0,
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/blueprints/bar/edit'
                ],
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('blueprints.index'))
            ->assertRedirect('/cp/original');
    }

    private function createBlueprint($handle)
    {
        return tap(new Blueprint)->setHandle($handle);
    }
}
