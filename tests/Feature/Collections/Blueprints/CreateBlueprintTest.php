<?php

namespace Tests\Feature\Collections\Blueprints;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Collection;
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
        tap(Collection::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('collections.blueprints.create', 'test'))
            ->assertOk()
            ->assertViewIs('statamic::collections.blueprints.create');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        tap(Collection::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('collections.blueprints.create', 'test'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
