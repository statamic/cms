<?php

namespace Tests\Feature\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->submit()
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'You are not authorized to create navs.');
    }

    #[Test]
    public function it_stores_a_nav()
    {
        $this->assertCount(0, Nav::all());

        $this
            ->actingAs($this->userWithPermission())
            ->submit($this->validParams())
            ->assertOk()
            ->assertJson(['redirect' => cp_route('navigation.show', 'test')]);

        $this->assertCount(1, Nav::all());
        $nav = Nav::all()->first();
        $this->assertEquals('test', $nav->handle());
    }

    #[Test]
    public function title_is_required()
    {
        $this->assertCount(0, Nav::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->submit($this->validParams([
                'title' => '',
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, Nav::all());
    }

    #[Test]
    public function handle_must_be_alpha_dash()
    {
        $this->assertCount(0, Nav::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->submit($this->validParams([
                'handle' => 'there are spaces in here',
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');

        $this->assertCount(0, Nav::all());
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test Nav',
            'handle' => 'test',
        ], $overrides);
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function submit($params = [])
    {
        return $this->post(cp_route('navigation.store'), $params);
    }
}
