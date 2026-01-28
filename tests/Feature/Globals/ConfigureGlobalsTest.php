<?php

namespace Tests\Feature\Globals;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConfigureGlobalsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $global = GlobalSet::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($global->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_shows_the_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure globals']]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalSet::make('test')->save();

        $this
            ->actingAs($user)
            ->get($global->editUrl())
            ->assertSuccessful();
    }
}
