<?php

namespace Tests\OAuth;

use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\Auth\Eloquent\User as EloquentUser;
use Tests\ElevatesSessions;
use Tests\TestCase;

class EloquentOAuthTest extends TestCase
{
    use ElevatesSessions;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => EloquentUser::class,
        ]);
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', ['test' => 'Test']);
        $app['config']->set('statamic.users.repository', 'eloquent');
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Auth/Eloquent/__migrations__');
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    #[Test]
    public function an_authenticated_eloquent_user_can_connect_a_provider()
    {
        $user = $this->makeUser();

        $this->assertFalse(method_exists($user, 'id'));

        $this->fakeProvider('sub-1');

        $response = $this
            ->actingAs($user)
            ->withElevatedSession()
            ->withSession(['statamic.oauth.guard' => 'web'])
            ->get(route('statamic.oauth.callback', 'test'));

        $this->assertSame((string) $user->getKey(), $this->provider()->getUserId('sub-1'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('success', __('statamic::messages.oauth_connected', ['provider' => 'Test']));
    }

    #[Test]
    public function reconnecting_a_provider_to_the_same_eloquent_user_is_idempotent()
    {
        $user = $this->makeUser();
        $this->provider()->setUserProviderId(UserFacade::fromUser($user), 'sub-1');

        $this->fakeProvider('sub-1');

        $response = $this
            ->actingAs($user)
            ->withElevatedSession()
            ->withSession(['statamic.oauth.guard' => 'web'])
            ->get(route('statamic.oauth.callback', 'test'));

        $this->assertSame((string) $user->getKey(), $this->provider()->getUserId('sub-1'));
        $response->assertSessionHas('success', __('statamic::messages.oauth_already_connected', ['provider' => 'Test']));
    }

    #[Test]
    public function an_authenticated_eloquent_user_can_disconnect_a_provider()
    {
        $user = $this->makeUser();
        $this->provider()->setUserProviderId(UserFacade::fromUser($user), 'sub-1');

        $response = $this
            ->actingAs($user)
            ->withElevatedSession()
            ->delete(route('statamic.oauth.disconnect', 'test'));

        $this->assertNull($this->provider()->getUserId('sub-1'));
        $response->assertSessionHas('success', __('statamic::messages.oauth_disconnected', ['provider' => 'Test']));
    }

    private function makeUser(): EloquentUser
    {
        return EloquentUser::create(['name' => 'Test User', 'email' => 'test@example.com']);
    }

    private function fakeProvider(string $id): void
    {
        Socialite::fake('test', SocialiteUser::fake([
            'id' => $id,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]));
    }

    private function provider(): Provider
    {
        return new Provider('test');
    }
}
