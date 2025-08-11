<?php

namespace Tests\Feature\Taxonomies\Blueprints;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Taxonomy;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Taxonomy::make('test'))->save();
        $blueprint = $collection->termBlueprint();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('blueprints.taxonomies.edit', [$collection, $blueprint]))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_provides_the_blueprint()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Taxonomy::make('test'))->save();
        $blueprint = $collection->termBlueprint();

        $this
            ->actingAs($user)
            ->get(cp_route('blueprints.taxonomies.edit', [$collection, $blueprint]))
            ->assertStatus(200)
            ->assertViewHas('blueprint', $blueprint);
    }
}
