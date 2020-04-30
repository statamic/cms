<?php

namespace Tests\Feature\Blueprints;

use Statamic\Auth\User;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewBlueprintListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_shows_a_list_of_fieldsets()
    {
        // When the CP header loads the avatar it reaches for the user blueprint.
        Facades\Blueprint::shouldReceive('find')->with('user')->andReturn(new Blueprint);

        Facades\Blueprint::shouldReceive('all')->andReturn(collect([
            'foo' => $blueprintA = $this->createBlueprint('foo'),
            'bar' => $blueprintB = $this->createBlueprint('bar'),
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
                    'edit_url' => 'http://localhost/cp/fields/blueprints/foo/edit',
                    'delete_url' => 'http://localhost/cp/fields/blueprints/foo',
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'sections' => 0,
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/blueprints/bar/edit',
                    'delete_url' => 'http://localhost/cp/fields/blueprints/bar',
                ],
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
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
