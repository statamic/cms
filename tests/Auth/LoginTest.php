<?php

namespace Tests\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationChallenged;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class LoginTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_login_page()
    {
        $this
            ->get(cp_route('login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/Login'));
    }

    #[Test]
    public function it_doesnt_show_the_login_page_when_authenticated()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('login'))
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function it_allows_logging_in()
    {
        $user = $this->user();

        $this
            ->assertGuest()
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'secret',
                'remember' => true,
            ])
            ->assertRedirect(cp_route('index'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_doesnt_allow_logging_in_with_invalid_credentials()
    {
        $user = $this->user();

        $this
            ->assertGuest()
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'invalid-password',
                'remember' => true,
            ])
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    #[Test]
    public function it_redirects_to_the_two_factor_challenge_page()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->assertGuest()
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'secret',
                'remember' => true,
            ])
            ->assertRedirect(cp_route('two-factor-challenge'))
            ->assertSessionHas('login.id', $user->id())
            ->assertSessionHas('login.remember', true);

        $this->assertGuest();

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    #[DefineEnvironment('disableTwoFactor')]
    public function it_skips_two_factor_challenge_when_two_factor_is_disabled()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this->withoutExceptionHandling();

        $this
            ->assertGuest()
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'secret',
            ])
            ->assertRedirect(cp_route('index'));

        $this->assertAuthenticatedAs($user);

        Event::assertNotDispatched(TwoFactorAuthenticationChallenged::class);
    }

    #[Test]
    public function it_redirects_to_intended_url()
    {
        $user = $this->user();

        $this
            ->assertGuest()
            ->session(['url.intended' => 'http://localhost/cp/cp/collections'])
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'secret',
            ])
            ->assertRedirect('http://localhost/cp/cp/collections');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_stores_the_intended_url_when_redirected_to_login()
    {
        $this
            ->get(cp_route('collections.index'))
            ->assertSessionHas('url.intended', 'http://localhost/cp/collections');
    }

    #[Test]
    public function it_can_logout()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function it_can_logout_with_redirect()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('logout').'?redirect=/cp')
            ->assertRedirect('/cp');

        $this->assertGuest();
    }

    #[Test]
    public function it_does_not_redirect_to_external_url_on_logout()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('logout').'?redirect=https://evil.com')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function it_cant_logout_when_unauthenticated()
    {
        $this
            ->get(cp_route('logout'))
            ->assertRedirect();

        $this->assertGuest();
    }

    protected function disableTwoFactor($app)
    {
        $app['config']->set('statamic.users.two_factor_enabled', false);
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('david@hasselhoff.com')->password('secret'))->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_confirmed_at' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }
}
