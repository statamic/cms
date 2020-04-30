<?php

namespace Tests\Feature\Navigation;

use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $nav = $this->createNav();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->submit($nav)
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'You are not authorized to configure navs.');
    }

    /** @test */
    public function it_updates_a_nav()
    {
        $nav = $this->createNav();
        $this->assertCount(1, Nav::all());

        $this
            ->actingAs($this->userWithPermission())
            ->submit($nav, $this->validParams())
            ->assertOk()
            ->assertJson(['title' => 'Updated']);

        $this->assertCount(1, Nav::all());
        $updated = Nav::all()->first();
        $this->assertEquals('Updated', $updated->title());
        $this->assertEquals(2, $updated->maxDepth());
        $this->assertTrue($updated->expectsRoot());
    }

    /** @test */
    public function it_updates_a_nav_with_multiple_sites()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
            'de' => ['url' => 'http://localhost/de/', 'locale' => 'de'],
        ]]);

        $nav = $this->createNav();
        $nav->addTree($nav->makeTree('de'));
        $this->assertCount(1, Nav::all());
        $this->assertEquals(['en', 'de'], Nav::all()->first()->trees()->keys()->all());

        $this
            ->actingAs($this->userWithPermission())
            ->submit($nav, $this->validParams(['sites' => [
                'en', 'fr', // starts with en+de, but should remove de and add fr, ending with en+fr
            ]]))
            ->assertOk()
            ->assertJson(['title' => 'Updated']);

        $this->assertCount(1, Nav::all());
        $updated = Nav::all()->first();
        $this->assertEquals('Updated', $updated->title());
        $this->assertEquals(2, $updated->maxDepth());
        $this->assertTrue($updated->expectsRoot());
        $this->assertEquals(['en', 'fr'], $updated->trees()->keys()->all());
    }

    /** @test */
    public function title_is_required()
    {
        $nav = $this->createNav();
        $this->assertCount(1, Nav::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->submit($nav, $this->validParams(['title' => '']))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(1, Nav::all());
        $nav = Nav::all()->first();
        $this->assertEquals('Existing', $nav->title());
        $this->assertEquals(1, $nav->maxDepth());
        $this->assertFalse($nav->expectsRoot());
    }

    private function createNav()
    {
        return Nav::make('test')
            ->title('Existing')
            ->maxDepth(1)
            ->expectsRoot(false)
            ->tap(function ($nav) {
                $nav->addTree($nav->makeTree('en'));
                $nav->save();
            });
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Updated',
            'collections' => ['pages'],
            'root' => true,
            'max_depth' => 2,
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

    private function submit($nav, $params = [])
    {
        return $this->patch($nav->showUrl(), $params);
    }
}
