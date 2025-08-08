<?php

namespace Tests\Feature\Collections\Blueprints;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewBlueprintListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_a_list_of_blueprints()
    {
        // When the CP header loads the avatar it reaches for the user blueprint.
        Facades\Blueprint::shouldReceive('find')->with('user')->andReturn(new Blueprint);

        Facades\Blueprint::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'foo' => $blueprintA = $this->createBlueprint('foo'),
            'bar' => $blueprintB = $this->createBlueprint('bar'),
        ]));

        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Collection::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('blueprints.collections.index', 'test'))
            ->assertSuccessful()
            ->assertViewHas('blueprints', collect([
                [
                    'id' => 'foo',
                    'handle' => 'foo',
                    'title' => 'Foo',
                    'tabs' => 2,
                    'fields' => 2,
                    'hidden' => false,
                    'edit_url' => 'http://localhost/cp/fields/blueprints/collections/test/foo/edit',
                    'delete_url' => 'http://localhost/cp/fields/blueprints/collections/test/foo',
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'tabs' => 2,
                    'fields' => 2,
                    'hidden' => false,
                    'edit_url' => 'http://localhost/cp/fields/blueprints/collections/test/bar/edit',
                    'delete_url' => 'http://localhost/cp/fields/blueprints/collections/test/bar',
                ],
            ]))
            ->assertDontSee('no-results');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Collection::make('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('blueprints.collections.index', 'test'))
            ->assertRedirect('/cp/original');
    }

    private function createBlueprint($handle)
    {
        return tap(new Blueprint)->setHandle($handle);
    }
}
