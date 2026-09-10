<?php

namespace Tests\OAuth;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User as UserFacade;
use Statamic\Http\Middleware\AuthGuard;
use Statamic\OAuth\Provider;
use Tests\ElevatesSessions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthDisconnectTest extends TestCase
{
    use ElevatesSessions, PreventSavingStacheItemsToDisk;

    protected function defineEnvironment($app)
    {
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', ['test' => 'Test']);
    }

    protected function publishedConfigWithoutDisconnectRoute($app)
    {
        // An older published config will have a `routes` array without the
        // `disconnect` key. Config is merged shallowly, so the key won't be
        // backfilled from ours — the route must fall back to a default.
        $app['config']->set('statamic.oauth.routes', [
            'login' => 'oauth/{provider}',
            'callback' => 'oauth/{provider}/callback',
        ]);
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

        $response = $this->actingAs($user)->withElevatedSession()->delete(route('statamic.oauth.disconnect', 'test'));

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
            ->withElevatedSession()
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
    #[DefineEnvironment('publishedConfigWithoutDisconnectRoute')]
    public function the_disconnect_route_falls_back_when_absent_from_a_published_config()
    {
        $this->assertTrue(Route::has('statamic.oauth.disconnect'));
        $this->assertEquals(url('oauth/test/disconnect'), route('statamic.oauth.disconnect', 'test'));
    }

    #[Test]
    public function the_front_end_disconnect_route_requires_a_delete_request_and_the_web_guard()
    {
        $route = app('router')->getRoutes()->getByName('statamic.oauth.disconnect');

        // A non-GET, CSRF-protected verb authenticated against the front-end guard.
        $this->assertContains('DELETE', $route->methods());
        $this->assertNotContains('GET', $route->methods());
        $this->assertContains(AuthGuard::class, $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
    }

    #[Test]
    public function a_control_panel_user_can_disconnect_through_the_cp_route()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->makeSuper()->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->withElevatedSession()
            ->delete(cp_route('oauth.disconnect', 'test'))
            ->assertRedirect();

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_cp_disconnect_route_is_a_delete_behind_control_panel_auth()
    {
        $route = app('router')->getRoutes()->getByName('statamic.cp.oauth.disconnect');

        $this->assertNotNull($route);
        $this->assertContains('DELETE', $route->methods());
        $this->assertNotContains('GET', $route->methods());
        $this->assertContains('statamic.cp.authenticated', $route->gatherMiddleware());
    }

    #[Test]
    public function the_front_end_disconnect_requires_an_elevated_session_when_enabled()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->delete(route('statamic.oauth.disconnect', 'test'))
            ->assertRedirect(route('statamic.elevated-session'));

        // Still connected — the disconnect was blocked.
        $this->assertEquals('user-1', (new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_front_end_disconnect_returns_a_403_for_json_when_not_elevated()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->deleteJson(route('statamic.oauth.disconnect', 'test'))
            ->assertJson(['message' => 'Requires an elevated session.'])
            ->assertStatus(403);

        $this->assertEquals('user-1', (new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_front_end_disconnect_succeeds_with_an_elevated_session()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->withElevatedSession()
            ->delete(route('statamic.oauth.disconnect', 'test'))
            ->assertSessionHas('success', __('statamic::messages.oauth_disconnected', ['provider' => 'Test']));

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_front_end_disconnect_succeeds_without_a_challenge_when_elevated_sessions_are_disabled()
    {
        config(['statamic.users.elevated_sessions_enabled' => false]);

        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->delete(route('statamic.oauth.disconnect', 'test'))
            ->assertSessionHas('success', __('statamic::messages.oauth_disconnected', ['provider' => 'Test']));

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_cp_disconnect_requires_an_elevated_session_when_enabled()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->makeSuper()->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->delete(cp_route('oauth.disconnect', 'test'))
            ->assertRedirect(cp_route('confirm-password'));

        $this->assertEquals('user-1', (new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_cp_disconnect_succeeds_with_an_elevated_session()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->makeSuper()->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->withElevatedSession()
            ->delete(cp_route('oauth.disconnect', 'test'))
            ->assertRedirect();

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));
    }

    #[Test]
    public function the_cp_disconnect_succeeds_without_a_challenge_when_elevated_sessions_are_disabled()
    {
        config(['statamic.users.elevated_sessions_enabled' => false]);

        $user = UserFacade::make()->id('user-1')->email('one@example.com')->makeSuper()->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->actingAs($user)
            ->delete(cp_route('oauth.disconnect', 'test'))
            ->assertRedirect();

        $this->assertNull((new Provider('test'))->getUserId('sub-1'));
    }
}
