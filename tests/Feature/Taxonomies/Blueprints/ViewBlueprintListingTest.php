<?php

namespace Tests\Feature\Taxonomies\Blueprints;

use Statamic\Facades;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewBlueprintListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_shows_a_list_of_blueprints()
    {
        // When the CP header loads the avatar it reaches for the user blueprint.
        Facades\Blueprint::shouldReceive('find')->with('user')->andReturn(new Blueprint);

        Facades\Blueprint::shouldReceive('in')->with('taxonomies/test')->andReturn(collect([
            'foo' => $blueprintA = $this->createBlueprint('foo'),
            'bar' => $blueprintB = $this->createBlueprint('bar'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Taxonomy::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('taxonomies.blueprints.index', 'test'))
            ->assertSuccessful()
            ->assertViewHas('blueprints', collect([
                [
                    'id' => 'foo',
                    'handle' => 'foo',
                    'title' => 'Foo',
                    'sections' => 2,
                    'fields' => 2,
                    'edit_url' => 'http://localhost/cp/taxonomies/test/blueprints/foo/edit',
                    'delete_url' => 'http://localhost/cp/taxonomies/test/blueprints/foo',
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'sections' => 2,
                    'fields' => 2,
                    'edit_url' => 'http://localhost/cp/taxonomies/test/blueprints/bar/edit',
                    'delete_url' => 'http://localhost/cp/taxonomies/test/blueprints/bar',
                ],
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Taxonomy::make('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('taxonomies.blueprints.index', 'test'))
            ->assertRedirect('/cp/original');
    }

    private function createBlueprint($handle)
    {
        return tap(new Blueprint)->setHandle($handle);
    }
}
