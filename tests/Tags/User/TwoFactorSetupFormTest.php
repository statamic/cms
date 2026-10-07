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
class TwoFactorSetupFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_for_user_with_pending_setup()
    {
        $user = $this->userWithTwoFactorPending();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_setup_form }}{{ qr_code }}{{ secret_key }}{{ /user:two_factor_setup_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/two-factor/confirm">', $output);
        $this->assertStringContainsString('<svg', $output);
        $this->assertStringContainsString('</svg>', $output);
    }

    #[Test]
    public function it_does_not_render_without_pending_setup()
    {
        $user = $this->user();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_setup_form }}<p>inside</p>{{ /user:two_factor_setup_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_provides_qr_code_url()
    {
        $user = $this->userWithTwoFactorPending();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_setup_form }}{{ qr_code_url }}{{ /user:two_factor_setup_form }}');

        $this->assertStringContainsString('data:image/svg+xml;base64,', $output);
    }

    #[Test]
    public function it_does_not_render_for_user_with_2fa_enabled()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_setup_form }}<p>inside</p>{{ /user:two_factor_setup_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_does_not_render_for_guests()
    {
        $output = $this->tag('{{ user:two_factor_setup_form }}<p>inside</p>{{ /user:two_factor_setup_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $user = $this->userWithTwoFactorPending();

        $this->actingAs($user);

        $form = Statamic::tag('user:two_factor_setup_form')->fetch();

        $this->assertEquals('http://localhost/!/auth/two-factor/confirm', $form['attrs']['action']);
        $this->assertEquals('POST', $form['attrs']['method']);
        $this->assertArrayHasKey('qr_code', $form);
        $this->assertArrayHasKey('qr_code_url', $form);
        $this->assertArrayHasKey('secret_key', $form);
    }

    #[Test]
    public function it_renders_with_redirect_params()
    {
        $user = $this->userWithTwoFactorPending();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_setup_form redirect="/dashboard" error_redirect="/error" }}{{ /user:two_factor_setup_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/dashboard" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/error" />', $output);
    }

    #[Test]
    public function it_confirms_setup_and_redirects()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->post(route('statamic.users.two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionHas('user.two_factor_setup.success');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_requires_elevated_session_to_confirm()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->post(route('statamic.users.two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect(route('statamic.elevated-session'));

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_redirects_back_without_redirect_param()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/setup-2fa')
            ->post(route('statamic.users.two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect('/setup-2fa')
            ->assertSessionHas('user.two_factor_setup.success');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_returns_json_for_xhr_requests()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->postJson(route('statamic.users.two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertOk()
            ->assertJson([]);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_redirects_back_with_errors_on_invalid_code()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/setup-2fa')
            ->post(route('statamic.users.two-factor.confirm'), [
                'code' => '123456',
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/setup-2fa')
            ->assertSessionHasErrors('code', null, 'user.two_factor_setup');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_redirects_to_error_redirect_on_invalid_code()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->post(route('statamic.users.two-factor.confirm'), [
                'code' => '123456',
                '_redirect' => '/dashboard',
                '_error_redirect' => '/setup-error',
            ])
            ->assertRedirect('/setup-error')
            ->assertSessionHasErrors('code', null, 'user.two_factor_setup');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_uses_intended_url_from_session_when_redirect_param_is_empty()
    {
        $user = $this->userWithTwoFactorPending();

        $this
            ->actingAs($user)
            ->session([
                'statamic_elevated_session' => now()->timestamp,
                'url.intended' => '/account',
            ])
            ->post(route('statamic.users.two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
                '_redirect' => '',
            ])
            ->assertRedirect('/account');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    private function withActiveElevatedSession()
    {
        return $this->session(['statamic_elevated_session' => now()->timestamp]);
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

    private function userWithTwoFactorPending()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
        ]);

        $user->save();

        return $user;
    }

    private function getOneTimeCode($user): string
    {
        return app(Google2FA::class)->getCurrentOtp($user->twoFactorSecretKey());
    }
}
