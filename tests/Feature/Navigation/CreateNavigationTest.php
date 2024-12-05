<?php

namespace Tests\Feature\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_create_page_if_you_have_permission()
    {
        $this->withoutExceptionHandling();
        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->visitCreatePage()
            ->assertOk()
            ->assertViewIs('statamic::navigation.create')
            ->assertSee('Create Navigation');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->visitCreatePage()
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    public function visitCreatePage()
    {
        return $this->get(cp_route('navigation.create'));
    }
}
