<?php

namespace Tests\Feature\Collections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_create_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('collections.create'))
            ->assertSuccessful();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('collections.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
