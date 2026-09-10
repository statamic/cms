<?php

namespace Tests\OAuth;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthPageTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function defineEnvironment($app)
    {
        $app['config']->set('statamic.oauth.providers', [
            'test' => 'Test',
            'another' => 'Another',
            'stateless' => ['stateless' => true, 'label' => 'Stateless'],
        ]);
    }

    protected function enableOAuth($app)
    {
        $app['config']->set('statamic.oauth.enabled', true);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    private function provider(string $name): Provider
    {
        return new Provider($name);
    }

    #[Test]
    #[DefineEnvironment('enableOAuth')]
    public function it_renders_the_connected_accounts_page_with_link_state_excluding_stateless_providers()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->makeSuper()->save();
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this
            ->actingAs($user)
            ->get(cp_route('oauth'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/OAuth')
                ->has('providers', 2)
                ->where('providers.0.name', 'test')
                ->where('providers.0.label', 'Test')
                ->where('providers.0.connected', true)
                ->where('providers.0.disconnectUrl', cp_route('oauth.disconnect', 'test'))
                ->where('providers.1.name', 'another')
                ->where('providers.1.connected', false)
            );
    }

    #[Test]
    #[DefineEnvironment('enableOAuth')]
    public function the_page_route_is_registered_when_oauth_is_enabled()
    {
        $this->assertTrue(Route::has('statamic.cp.oauth'));
    }

    #[Test]
    public function the_page_route_is_not_registered_when_oauth_is_disabled()
    {
        $this->assertFalse(Route::has('statamic.cp.oauth'));
    }
}
