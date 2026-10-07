<?php

namespace Tests\Tags\User;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class DisableTwoFactorFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_for_user_with_2fa()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:disable_two_factor_form }}{{ /user:disable_two_factor_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/two-factor/disable">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE"', $output);
        $this->assertStringContainsString(csrf_field(), $output);
    }

    #[Test]
    public function it_does_not_render_for_user_without_2fa()
    {
        $user = $this->user();

        $this->actingAs($user);

        $output = $this->tag('{{ user:disable_two_factor_form }}<p>inside</p>{{ /user:disable_two_factor_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_does_not_render_for_guests()
    {
        $output = $this->tag('{{ user:disable_two_factor_form }}<p>inside</p>{{ /user:disable_two_factor_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_renders_with_redirect_param()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:disable_two_factor_form redirect="/account" }}{{ /user:disable_two_factor_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/account" />', $output);
    }

    #[Test]
    public function it_disables_2fa_and_redirects()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->session(['statamic_elevated_session' => now()->timestamp])
            ->delete(route('statamic.users.two-factor.disable'), [
                '_redirect' => '/account',
            ])
            ->assertRedirect('/account')
            ->assertSessionHas('user.two_factor_disable.success');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_requires_elevated_session_to_disable()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(route('statamic.users.two-factor.disable'), [
                '_redirect' => '/account',
            ])
            ->assertRedirect(route('statamic.elevated-session'));
    }

    #[Test]
    public function it_redirects_to_configured_setup_url_when_2fa_is_enforced()
    {
        config([
            'statamic.users.two_factor_enforced_roles' => ['*'],
            'statamic.users.two_factor_setup_url' => '/auth/setup-2fa',
        ]);

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->session(['statamic_elevated_session' => now()->timestamp])
            ->delete(route('statamic.users.two-factor.disable'), [
                '_redirect' => '/account',
            ])
            ->assertRedirect('/auth/setup-2fa')
            ->assertSessionHas('user.two_factor_disable.success');
    }

    #[Test]
    public function it_falls_back_to_default_setup_route_when_no_config_and_2fa_is_enforced()
    {
        config(['statamic.users.two_factor_enforced_roles' => ['*']]);

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->session(['statamic_elevated_session' => now()->timestamp])
            ->delete(route('statamic.users.two-factor.disable'), [
                '_redirect' => '/account',
            ])
            ->assertRedirect(route('statamic.two-factor-setup'));
    }

    #[Test]
    public function it_prefers_redirect_param_over_intended_url_in_session()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->session([
                'statamic_elevated_session' => now()->timestamp,
                'url.intended' => '/dashboard',
            ])
            ->delete(route('statamic.users.two-factor.disable'), [
                '_redirect' => '/account',
            ])
            ->assertRedirect('/account');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_redirects_back_without_redirect_param()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->session(['statamic_elevated_session' => now()->timestamp])
            ->from('/account')
            ->delete(route('statamic.users.two-factor.disable'))
            ->assertRedirect('/account')
            ->assertSessionHas('user.two_factor_disable.success');

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    #[Test]
    public function it_returns_json_for_xhr_requests()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->session(['statamic_elevated_session' => now()->timestamp])
            ->deleteJson(route('statamic.users.two-factor.disable'))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $this->assertNull($user->fresh()->two_factor_confirmed_at);
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
}
