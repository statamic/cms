<?php

namespace Tests\OAuth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationChallenged;
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
        $app['config']->set('statamic.oauth.providers', [
            'test' => 'Test',
            'stateless' => ['stateless' => true, 'label' => 'Stateless'],
        ]);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    #[Test]
    public function guest_with_a_new_email_is_created_and_logged_in()
    {
        $this->assertCount(0, UserFacade::all());

        $this->fakeProvider('test', [], 'sub-1', 'new@example.com');

        $this->hitCallback('test');

        $this->assertCount(1, UserFacade::all());
        $this->assertNotNull($user = UserFacade::findByEmail('new@example.com'));
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($user->id(), $this->provider('test')->getUserId('sub-1'));
    }

    #[Test]
    public function guest_matching_an_oauth_id_is_logged_in_without_creating_a_user()
    {
        $user = UserFacade::make()->id('user-1')->email('existing@example.com')->save();
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'existing@example.com');

        $this->hitCallback('test');

        $this->assertCount(1, UserFacade::all());
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function guest_whose_email_already_exists_is_denied_and_not_logged_in()
    {
        UserFacade::make()->id('user-1')->email('taken@example.com')->save();

        // A different oauth id, but an email that already belongs to an account.
        $this->fakeProvider('test', [], 'sub-new', 'taken@example.com');

        $response = $this->hitCallback('test');

        $this->assertGuest();
        $this->assertCount(1, UserFacade::all());
        $this->assertNull($this->provider('test')->getUserId('sub-new'));
        $response->assertSessionHas('error', __('statamic::messages.oauth_email_exists'));
    }

    #[Test]
    public function authenticated_user_links_a_provider()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();

        $this->fakeProvider('test', [], 'sub-1', 'one@example.com');

        $response = $this->actingAs($user)->hitCallback('test');

        $this->assertEquals('user-1', $this->provider('test')->getUserId('sub-1'));
        $this->assertCount(1, UserFacade::all());
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('success', __('statamic::messages.oauth_linked', ['provider' => 'Test']));
    }

    #[Test]
    public function linking_a_provider_already_linked_to_the_user_is_idempotent()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'one@example.com');

        $response = $this->actingAs($user)->hitCallback('test');

        $this->assertEquals('user-1', $this->provider('test')->getUserId('sub-1'));
        $response->assertSessionHas('success', __('statamic::messages.oauth_link_already_connected', ['provider' => 'Test']));
    }

    #[Test]
    public function it_does_not_link_a_provider_identity_owned_by_another_user()
    {
        $other = UserFacade::make()->id('other')->email('other@example.com')->save();
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        $this->provider('test')->setUserProviderId($other, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'one@example.com');

        $response = $this->actingAs($user)->hitCallback('test');

        // Still belongs to the original owner.
        $this->assertEquals('other', $this->provider('test')->getUserId('sub-1'));
        $response->assertSessionHas('error', __('statamic::messages.oauth_link_belongs_to_another_user', ['provider' => 'Test']));
    }

    #[Test]
    public function it_does_not_link_a_stateless_provider()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();

        $this->fakeProvider('stateless', ['stateless' => true], 'sub-1', 'one@example.com');

        $response = $this->actingAs($user)->hitCallback('stateless');

        $this->assertNull($this->provider('stateless')->getUserId('sub-1'));
        $response->assertSessionHas('error', __('statamic::messages.oauth_link_unsupported'));
    }

    #[Test]
    public function logging_in_merges_user_data_when_enabled()
    {
        $user = UserFacade::make()->id('user-1')->email('existing@example.com')->data(['name' => 'Old Name'])->save();
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'existing@example.com', 'New Name');

        $this->hitCallback('test');

        $this->assertAuthenticatedAs($user = UserFacade::find('user-1'));
        $this->assertEquals('New Name', $user->get('name'));
    }

    #[Test]
    public function logging_in_does_not_merge_user_data_when_disabled()
    {
        config()->set('statamic.oauth.merge_user_data', false);

        $user = UserFacade::make()->id('user-1')->email('existing@example.com')->data(['name' => 'Old Name'])->save();
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'existing@example.com', 'New Name');

        $this->hitCallback('test');

        $this->assertAuthenticatedAs($user = UserFacade::find('user-1'));
        $this->assertEquals('Old Name', $user->get('name'));
    }

    #[Test]
    public function a_guest_with_a_new_email_is_not_created_when_user_creation_is_disabled()
    {
        config()->set('statamic.oauth.create_user', false);

        $this->assertCount(0, UserFacade::all());

        $this->fakeProvider('test', [], 'sub-1', 'new@example.com');

        $response = $this->hitCallback('test');

        $this->assertGuest();
        $this->assertCount(0, UserFacade::all());
        $this->assertNull($this->provider('test')->getUserId('sub-1'));
        $response->assertRedirect();
    }

    #[Test]
    public function a_two_factor_enabled_user_is_challenged_instead_of_being_logged_in()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled('user-1', 'existing@example.com');
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'existing@example.com');

        $response = $this->hitCallback('test');

        $this->assertGuest();
        $response->assertRedirect(route('statamic.two-factor-challenge'));
        $response->assertSessionHas('login.id', 'user-1');
        Event::assertDispatched(TwoFactorAuthenticationChallenged::class, fn ($event) => $event->user->id() === 'user-1');
    }

    #[Test]
    public function a_two_factor_challenge_from_the_cp_redirects_to_the_cp_challenge()
    {
        $user = $this->userWithTwoFactorEnabled('user-1', 'existing@example.com');
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'existing@example.com');

        $response = $this
            ->withSession([
                'statamic.oauth.guard' => 'web',
                '_previous' => ['url' => 'http://localhost/oauth/test?redirect=/cp'],
            ])
            ->get(route('statamic.oauth.callback', 'test'));

        $this->assertGuest();
        $response->assertRedirect(cp_route('two-factor-challenge'));
        $response->assertSessionHas('login.id', 'user-1');
    }

    #[Test]
    public function a_two_factor_enabled_user_is_logged_in_when_two_factor_is_disabled()
    {
        config()->set('statamic.users.two_factor_enabled', false);

        Event::fake();

        $user = $this->userWithTwoFactorEnabled('user-1', 'existing@example.com');
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->fakeProvider('test', [], 'sub-1', 'existing@example.com');

        $this->hitCallback('test');

        $this->assertAuthenticatedAs(UserFacade::find('user-1'));
        Event::assertNotDispatched(TwoFactorAuthenticationChallenged::class);
    }

    private function userWithTwoFactorEnabled(string $id, string $email)
    {
        $user = UserFacade::make()->id($id)->email($email)->save();

        $user->merge([
            'two_factor_confirmed_at' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())),
        ])->save();

        return $user;
    }

    private function hitCallback(string $provider)
    {
        return $this
            ->withSession(['statamic.oauth.guard' => 'web'])
            ->get(route('statamic.oauth.callback', $provider));
    }

    /**
     * Replace the provider with a real one whose only mocked method is
     * getSocialiteUser(), so the Socialite facade is never called but the
     * storage map and user lookups behave for real.
     */
    private function fakeProvider(string $name, array $config, string $id, string $email, string $displayName = 'Foo Bar'): void
    {
        $socialiteUser = new class($id, $email, $displayName)
        {
            public function __construct(private string $id, private string $email, private string $name)
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
                return $this->name;
            }
        };

        $provider = Mockery::mock(Provider::class.'[getSocialiteUser]', [$name, $config]);
        $provider->shouldReceive('getSocialiteUser')->andReturn($socialiteUser);

        OAuth::partialMock()->shouldReceive('provider')->with($name)->andReturn($provider);
    }

    private function provider(string $name): Provider
    {
        return new Provider($name);
    }
}
