<?php

namespace Tests\Tags\User;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class TwoFactorChallengeFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_form_when_login_id_in_session()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->session(['login.id' => $user->id()]);

        $output = $this->tag('{{ user:two_factor_challenge_form }}{{ /user:two_factor_challenge_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/two-factor-challenge">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    #[Test]
    public function it_does_not_render_without_login_id_in_session()
    {
        $output = $this->tag('{{ user:two_factor_challenge_form }}<p>inside</p>{{ /user:two_factor_challenge_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_renders_with_redirect_params()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->session(['login.id' => $user->id()]);

        $output = $this->tag('{{ user:two_factor_challenge_form redirect="/dashboard" error_redirect="/error" }}{{ /user:two_factor_challenge_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/dashboard" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/error" />', $output);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->session(['login.id' => $user->id()]);

        $form = Statamic::tag('user:two_factor_challenge_form')->fetch();

        $this->assertEquals('http://localhost/!/auth/two-factor-challenge', $form['attrs']['action']);
        $this->assertEquals('POST', $form['attrs']['method']);
        $this->assertArrayHasKey('_token', $form['params']);
    }

    #[Test]
    public function it_completes_challenge_and_redirects()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_clears_login_session_keys_after_successful_challenge()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id(), 'login.remember' => true])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionMissing('login.id')
            ->assertSessionMissing('login.remember');
    }

    #[Test]
    public function it_completes_challenge_with_recovery_code_and_redirects()
    {
        $user = $this->userWithTwoFactorEnabled();
        $codes = $user->twoFactorRecoveryCodes();

        $this
            ->session(['login.id' => $user->id()])
            ->post(route('statamic.two-factor-challenge'), [
                'recovery_code' => $codes[0],
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertNotContains($codes[0], $user->fresh()->twoFactorRecoveryCodes());
    }

    #[Test]
    public function it_redirects_to_home_without_redirect_param()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect(route('statamic.site'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_redirects_to_configured_challenge_url_on_invalid_code()
    {
        config(['statamic.users.two_factor_challenge_url' => '/two-factor-challenge']);

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => '123456',
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/two-factor-challenge')
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    #[Test]
    public function it_redirects_to_default_challenge_route_on_invalid_code_without_config()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => '123456',
            ])
            ->assertRedirect(route('statamic.two-factor-challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    #[Test]
    public function it_redirects_to_error_redirect_on_invalid_code()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => '123456',
                '_redirect' => '/dashboard',
                '_error_redirect' => '/challenge-error',
            ])
            ->assertRedirect('/challenge-error')
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    #[Test]
    public function it_uses_intended_url_from_session_when_no_redirect_param()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session([
                'login.id' => $user->id(),
                'url.intended' => '/account',
            ])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect('/account');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_clears_intended_url_from_session_when_redirect_param_wins()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session([
                'login.id' => $user->id(),
                'url.intended' => '/account',
            ])
            ->post(route('statamic.two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionMissing('url.intended');

        $this->assertAuthenticatedAs($user);
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('test@example.com'))->save();
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

    private function getOneTimeCode($user): string
    {
        return app(Google2FA::class)->getCurrentOtp($user->twoFactorSecretKey());
    }
}
