<?php

namespace Tests\Feature\Taxonomies\Blueprints;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Taxonomy;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Taxonomy::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('blueprints.taxonomies.create', 'test'))
            ->assertOk()
            ->assertViewIs('statamic::taxonomies.blueprints.create');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Taxonomy::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('blueprints.taxonomies.create', 'test'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
