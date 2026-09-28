<?php

namespace Tests\Feature\Navigation;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateNavigationPageTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function request($nav, $params = [], $site = 'en')
    {
        $url = cp_route('navigation.pages.update', $nav->handle());

        return $this->postJson($url, array_merge(['site' => $site], $params));
    }

    private function setNavBlueprint($nav)
    {
        $blueprint = Blueprint::makeFromFields([]);
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('navigation.'.$nav->handle())->andReturn($blueprint);
    }

    #[Test]
    public function it_denies_access_without_permission_to_view_the_nav()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [])->save();
        $this->setNavBlueprint($nav);

        $this
            ->actingAs($user)
            ->request($nav, [
                'type' => 'url',
                'values' => ['title' => 'The title', 'url' => 'http://example.com'],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function it_allows_access_with_permission_to_view_the_nav()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test nav']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [])->save();
        $this->setNavBlueprint($nav);

        $this
            ->actingAs($user)
            ->request($nav, [
                'type' => 'url',
                'values' => ['title' => 'The title', 'url' => 'http://example.com'],
            ])
            ->assertOk();
    }
}
