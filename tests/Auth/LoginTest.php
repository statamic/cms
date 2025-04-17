<?php

namespace Tests\Auth;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_login_page()
    {
        $this
            ->get(cp_route('login'))
            ->assertOk()
            ->assertViewIs('statamic::auth.login');
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
    }

    #[Test]
    public function it_redirects_to_referer_url()
    {
        $user = $this->user();

        $this
            ->assertGuest()
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'secret',
                'referer' => 'http://localhost/cp/cp/collections',
            ])
            ->assertRedirect('http://localhost/cp/cp/collections');

        $this->assertAuthenticatedAs($user);
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
    public function it_can_logout()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('logout'))
            ->assertRedirect();

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

    #[Test]
    public function it_clears_last_two_factor_challenged_timestamp_when_logging_out()
    {
        $user = $this->userWithTwoFactorEnabled();

        $user->setLastTwoFactorChallenged();
        $this->assertNotNull($user->getLastTwoFactorChallenged());

        $this
            ->actingAs($user)
            ->get(cp_route('logout'))
            ->assertRedirect();

        $this->assertNull($user->getLastTwoFactorChallenged());
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
            'two_factor_completed' => now()->timestamp,
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }
}
