<?php

namespace Tests\Tags\User;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationEnabled;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class TwoFactorEnableFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_for_authenticated_user_without_secret()
    {
        $user = $this->user();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_enable_form }}<button>Enable</button>{{ /user:two_factor_enable_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/two-factor/enable">', $output);
        $this->assertStringContainsString('<button>Enable</button>', $output);
    }

    #[Test]
    public function it_renders_for_user_with_pending_setup()
    {
        $user = $this->userWithTwoFactorPending();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_enable_form }}<p>inside</p>{{ /user:two_factor_enable_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/two-factor/enable">', $output);
        $this->assertStringContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_does_not_render_for_user_with_2fa_enabled()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_enable_form }}<p>inside</p>{{ /user:two_factor_enable_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_does_not_render_for_guests()
    {
        $output = $this->tag('{{ user:two_factor_enable_form }}<p>inside</p>{{ /user:two_factor_enable_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_renders_with_redirect_param()
    {
        $user = $this->user();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_enable_form redirect="/dashboard" }}{{ /user:two_factor_enable_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/dashboard" />', $output);
    }

    #[Test]
    public function it_enables_2fa_and_redirects_back()
    {
        Event::fake();

        $user = $this->user();

        $this->assertNull($user->two_factor_secret);

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'))
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh()->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_requires_elevated_session_to_enable()
    {
        $user = $this->user();

        $this
            ->actingAs($user)
            ->post(route('statamic.users.two-factor.enable'))
            ->assertRedirect(route('statamic.elevated-session'));

        $this->assertNull($user->fresh()->two_factor_secret);
    }

    #[Test]
    public function it_redirects_to_redirect_param()
    {
        $user = $this->user();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'), [
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function it_ignores_external_redirect_param()
    {
        $user = $this->user();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'), [
                '_redirect' => 'https://evil.example.com',
            ])
            ->assertRedirect('/profile');
    }

    #[Test]
    public function it_falls_back_to_configured_setup_url_without_redirect_param()
    {
        config(['statamic.users.two_factor_setup_url' => '/setup-2fa']);

        $user = $this->user();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'))
            ->assertRedirect('/setup-2fa');
    }

    #[Test]
    public function it_prefers_redirect_param_over_configured_setup_url()
    {
        config(['statamic.users.two_factor_setup_url' => '/setup-2fa']);

        $user = $this->user();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'), [
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function it_is_idempotent_when_secret_already_exists()
    {
        Event::fake();

        $user = $this->user();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'))
            ->assertRedirect('/profile');

        $firstSecret = $user->fresh()->two_factor_secret;
        $this->assertNotNull($firstSecret);

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->from('/profile')
            ->post(route('statamic.users.two-factor.enable'))
            ->assertRedirect('/profile');

        $this->assertEquals($firstSecret, $user->fresh()->two_factor_secret);

        Event::assertDispatchedTimes(TwoFactorAuthenticationEnabled::class, 1);
    }

    private function withActiveElevatedSession()
    {
        return $this->session(['statamic_elevated_session' => now()->timestamp]);
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('test@example.com'))->save();
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
