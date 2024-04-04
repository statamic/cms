<?php

namespace Tests\Feature\Blueprints;

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
    public function it_shows_a_list_of_blueprints()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('blueprints.index'))
            ->assertOk()
            ->assertViewIs('statamic::blueprints.index');
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

    /** @test */
    public function it_lets_you_edit_a_custom_namespace_blueprint()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $namespace = 'foo';
        $handle = 'bar';

        Facades\Blueprint::addNamespace($namespace, 'resources/content/'.$namespace);

        $this
            ->actingAs($user)
            ->get(cp_route('blueprints.edit', [$namespace, $handle]))
            ->assertOk()
            ->assertViewIs('statamic::blueprints.edit');
    }

    private function createBlueprint($handle)
    {
        return tap(new Blueprint)->setHandle($handle);
    }
}
