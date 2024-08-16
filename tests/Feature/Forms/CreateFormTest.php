<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_create_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.create'))
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
            ->get(cp_route('forms.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
