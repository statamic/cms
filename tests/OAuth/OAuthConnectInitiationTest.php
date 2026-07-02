<?php

namespace Tests\OAuth;

use Laravel\Socialite\Facades\Socialite;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\OAuth;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\ElevatesSessions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthConnectInitiationTest extends TestCase
{
    use ElevatesSessions, PreventSavingStacheItemsToDisk;

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
    public function an_authenticated_connect_requires_an_elevated_session_when_enabled()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();

        $this->actingAs($user)
            ->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect(route('statamic.elevated-session'));
    }

    #[Test]
    public function an_authenticated_connect_proceeds_to_the_provider_with_an_elevated_session()
    {
        Socialite::fake('test');

        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();

        $this->actingAs($user)
            ->withElevatedSession()
            ->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect('https://socialite.fake/test/authorize');
    }

    #[Test]
    public function an_authenticated_connect_proceeds_when_elevated_sessions_are_disabled()
    {
        Socialite::fake('test');

        config(['statamic.users.elevated_sessions_enabled' => false]);

        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();

        $this->actingAs($user)
            ->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect('https://socialite.fake/test/authorize');
    }

    #[Test]
    public function an_unauthenticated_sign_in_is_not_gated_by_elevated_sessions()
    {
        Socialite::fake('test');

        // A guest hitting the same route is signing in, not connecting, so the
        // elevation requirement must not apply even though it's enabled.
        $this->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect('https://socialite.fake/test/authorize');
    }

    #[Test]
    public function a_cp_connect_redirects_to_the_cp_elevation_challenge()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->makeSuper()->save();

        // The referer tells the controller this connect originated in the CP, so
        // the user should land on the CP challenge rather than the front-end one.
        $this->actingAs($user)
            ->get(route('statamic.oauth.login', 'test'), ['referer' => 'http://localhost/cp/users'])
            ->assertRedirect(cp_route('confirm-password'));
    }

    #[Test]
    public function a_connect_immediately_after_a_fresh_oauth_login_is_already_elevated()
    {
        Socialite::fake('test');

        // Logging in via OAuth elevates the session, so an immediate connect
        // should sail through without a fresh challenge.
        $user = UserFacade::make()->id('user-1')->email('existing@example.com')->save();
        (new Provider('test'))->setUserProviderId($user, 'sub-1');

        $this->fakeProviderForLogin('test', 'sub-1', 'existing@example.com');

        $this->withSession(['statamic.oauth.guard' => 'web'])
            ->get(route('statamic.oauth.callback', 'test'));

        $this->assertAuthenticatedAs(UserFacade::find('user-1'));

        $this->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect('https://socialite.fake/test/authorize');
    }

    /**
     * Replace the provider so the callback can log a user in for real without
     * touching the live Socialite driver.
     */
    private function fakeProviderForLogin(string $name, string $id, string $email): void
    {
        $socialiteUser = new class($id, $email)
        {
            public function __construct(private string $id, private string $email)
            {
            }

            public function getId()
            {
                return $this->id;
            }

            public function getEmail()
            {
                return $this->email;
            }

            public function getName()
            {
                return 'Foo Bar';
            }
        };

        $provider = Mockery::mock(Provider::class.'[getSocialiteUser]', [$name, []]);
        $provider->shouldReceive('getSocialiteUser')->andReturn($socialiteUser);

        OAuth::partialMock()->shouldReceive('provider')->with($name)->andReturn($provider);
    }
}
