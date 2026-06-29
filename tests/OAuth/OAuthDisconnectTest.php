<?php

namespace Tests\OAuth;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthDisconnectTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function defineEnvironment($app)
    {
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', ['test' => 'Test']);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    #[Test]
    public function it_disconnects_a_provider_from_the_authenticated_user()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        $other = UserFacade::make()->id('user-2')->email('two@example.com')->save();
        $provider = new Provider('test');
        $provider->setUserProviderId($user, 'sub-1');
        $provider->setUserProviderId($other, 'sub-2');

        $response = $this->actingAs($user)->delete(route('statamic.oauth.disconnect', 'test'));

        $response->assertSessionHas('success', __('statamic::messages.oauth_disconnected', ['provider' => 'Test']));

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));

        // Another user's connection to the same provider is untouched.
        $this->assertEquals('user-2', (new Provider('test'))->getUserId('sub-2'));
    }

    #[Test]
    public function it_returns_no_content_for_json_requests()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->deleteJson(route('statamic.oauth.disconnect', 'test'))
            ->assertNoContent();

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function guests_cannot_disconnect()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->deleteJson(route('statamic.oauth.disconnect', 'test'))->assertUnauthorized();

        $this->assertEquals('user-1', (new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_disconnect_route_requires_a_delete_request_and_authentication()
    {
        $route = app('router')->getRoutes()->getByName('statamic.oauth.disconnect');

        // A non-GET, CSRF-protected verb behind the auth middleware.
        $this->assertContains('DELETE', $route->methods());
        $this->assertNotContains('GET', $route->methods());
        $this->assertContains('auth', $route->gatherMiddleware());
    }
}
