<?php

namespace Tests\OAuth;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\OAuth;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthCallbackTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function defineEnvironment($app)
    {
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', ['evil', 'google']);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    private function fakeProvider(string $name)
    {
        $socialiteUser = new FakeSocialiteUser();

        $provider = Mockery::mock(Provider::class.'[getSocialiteUser]', [$name, []]);
        $provider->shouldReceive('getSocialiteUser')->andReturn($socialiteUser);

        OAuth::partialMock()->shouldReceive('provider')->with($name)->andReturn($provider);
    }

    #[Test]
    public function an_untrusted_provider_does_not_overwrite_or_log_into_an_existing_account()
    {
        config(['statamic.oauth.trusted_providers' => ['google']]); // 'evil' is untrusted
        config(['statamic.oauth.create_user' => true]);

        $admin = UserFacade::make()->email('admin@target.tld')->data(['name' => 'Admin'])->makeSuper()->save();

        $this->fakeProvider('evil');

        $response = $this->get('/oauth/evil/callback');

        // No login happened, and they were sent to the unauthorized redirect.
        $this->assertGuest();
        $response->assertRedirect();

        // The existing super admin is untouched and no duplicate was created.
        $this->assertCount(1, UserFacade::all());
        $admin = $admin->fresh();
        $this->assertTrue($admin->isSuper());
        $this->assertEquals('Admin', $admin->name());
    }

    #[Test]
    public function a_trusted_provider_logs_into_the_existing_account()
    {
        config(['statamic.oauth.trusted_providers' => ['google']]);

        $admin = UserFacade::make()->email('admin@target.tld')->data(['name' => 'Admin'])->makeSuper()->save();

        $this->fakeProvider('google');

        $this->get('/oauth/google/callback');

        $this->assertAuthenticatedAs($admin->fresh());
        $this->assertCount(1, UserFacade::all());
    }
}

class FakeSocialiteUser
{
    public function getId()
    {
        return 'attacker-1';
    }

    public function getName()
    {
        return 'Mallory';
    }

    public function getEmail()
    {
        return 'admin@target.tld';
    }
}
