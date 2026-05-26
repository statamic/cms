<?php

namespace Tests\Tags\User;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationChallenged;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
#[Group('passkeys')]
class LoginFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:login_form }}{{ /user:login_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/login">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ user:login_form redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /user:login_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/login" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    #[Test]
    public function it_renders_form_with_redirects_to_anchor()
    {
        $output = $this->tag('{{ user:login_form redirect="#form" error_redirect="#form" }}{{ /user:login_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    #[Test]
    public function it_wont_log_user_in_and_renders_errors()
    {
        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'leya',
            ])
            ->assertLocation('/');

        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Invalid credentials.'], $errors[1]);
        $this->assertEmpty($success[1]);
    }

    #[Test]
    public function it_will_log_user_in_and_render_success()
    {
        $this->assertFalse(auth()->check());

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
            ])
            ->assertLocation('/');

        $this->assertTrue(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}

    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Login successful.'], $success[1]);
    }

    #[Test]
    public function it_will_log_user_in_and_follow_custom_redirect_with_success()
    {
        $this->assertFalse(auth()->check());

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                '_redirect' => '/login-successful',
            ])
            ->assertLocation('/login-successful');

        $this->assertTrue(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Login successful.'], $success[1]);
    }

    #[Test]
    public function it_wont_log_user_in_and_follow_custom_error_redirect_with_errors()
    {
        $this->assertFalse(auth()->check());

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'wrong',
                '_error_redirect' => '/login-error',
            ])
            ->assertLocation('/login-error');

        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Invalid credentials.'], $errors[1]);
        $this->assertEmpty($success[1]);
    }

    #[Test]
    public function it_does_not_redirect_to_external_url()
    {
        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                '_redirect' => 'https://evil.com',
            ])
            ->assertLocation('/');
    }

    #[Test]
    public function it_does_not_redirect_to_external_url_on_error()
    {
        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'wrong',
                '_error_redirect' => 'https://evil.com',
            ])
            ->assertLocation('/');
    }

    #[Test]
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=login-successful&error_redirect=login-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="login-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="login-failure" />';

        $output = $this->tag('{{ user:login_form }}{{ /user:login_form }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ user:login_form allow_request_redirect="true" }}{{ /user:login_form }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $form = Statamic::tag('user:login_form')->fetch();

        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/auth/login');
        $this->assertEquals($form['attrs']['method'], 'POST');

        $this->assertArrayHasKey('_token', $form['params']);
    }

    #[Test]
    public function it_handles_precognitive_requests()
    {
        if (! method_exists($this, 'withPrecognition')) {
            $this->markTestSkipped();
        }

        $response = $this
            ->withPrecognition()
            ->postJson('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                '_error_redirect' => '/login-error',
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_redirects_to_the_two_factor_challenge_page()
    {
        Event::fake();

        $this->assertFalse(auth()->check());

        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->data([
                'two_factor_confirmed_at' => now()->timestamp,
                'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])
            ->save();

        $this
            ->assertGuest()
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
            ])
            ->assertRedirect('/!/auth/two-factor-challenge')
            ->assertSessionHas('login.id', 1)
            ->assertSessionHas('login.remember', false);

        $this->assertFalse(auth()->check());

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class, fn ($event) => $event->user->id === 1);
    }

    #[Test]
    public function it_redirects_to_configured_two_factor_challenge_url()
    {
        Event::fake();

        config(['statamic.users.two_factor_challenge_url' => '/custom-2fa-challenge']);

        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->data([
                'two_factor_confirmed_at' => now()->timestamp,
                'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])
            ->save();

        $this
            ->assertGuest()
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
            ])
            ->assertRedirect('/custom-2fa-challenge')
            ->assertSessionHas('login.id', 1);

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class);
    }

    #[Test]
    public function it_stores_redirect_as_intended_url_for_two_factor_challenge()
    {
        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->data([
                'two_factor_confirmed_at' => now()->timestamp,
                'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                '_redirect' => '/dashboard',
            ])
            ->assertSessionHas('url.intended', '/dashboard');
    }

    #[Test]
    public function it_redirects_to_intended_url_when_no_redirect_param_is_provided()
    {
        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->withSession(['url.intended' => '/protected'])
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
            ])
            ->assertRedirect('/protected')
            ->assertSessionMissing('url.intended');
    }

    #[Test]
    public function it_prefers_redirect_param_over_intended_url()
    {
        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->withSession(['url.intended' => '/protected'])
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionMissing('url.intended');
    }

    #[Test]
    public function it_stashes_redirect_as_intended_url_when_two_factor_setup_is_required()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                '_redirect' => '/dashboard',
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionHas('url.intended', '/dashboard');
    }

    #[Test]
    #[DefineEnvironment('disableTwoFactor')]
    public function it_skips_two_factor_challenge_when_two_factor_is_disabled()
    {
        Event::fake();

        $this->assertFalse(auth()->check());

        User::make()
            ->id(1)
            ->email('san@holo.com')
            ->password('chewy')
            ->data([
                'two_factor_confirmed_at' => now()->timestamp,
                'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])
            ->save();

        $this
            ->assertGuest()
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
            ])
            ->assertLocation('/');

        $this->assertTrue(auth()->check());

        Event::assertNotDispatched(TwoFactorAuthenticationChallenged::class);
    }

    protected function disableTwoFactor($app)
    {
        $app['config']->set('statamic.users.two_factor_enabled', false);
    }

    #[Test]
    public function it_includes_passkey_data()
    {
        $output = $this->tag('{{ user:login_form }}{{ passkey_options_url }}|{{ passkey_verify_url }}{{ /user:login_form }}');

        $this->assertStringContainsString(route('statamic.passkeys.options'), $output);
        $this->assertStringContainsString(route('statamic.passkeys.login'), $output);
    }

    #[Test]
    public function it_allows_password_login_when_user_has_no_passkeys()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $this
            ->post('/!/auth/login', [
                'email' => 'test@example.com',
                'password' => 'secret',
            ])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_blocks_password_login_when_user_has_passkeys_and_enforcement_enabled()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        $passkey = Mockery::mock(Passkey::class);
        $passkey->shouldReceive('id')->andReturn('passkey-1');
        $user->setPasskeys(collect([$passkey]));

        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $this
            ->from('/login')
            ->post('/!/auth/login', [
                'email' => 'test@example.com',
                'password' => 'secret',
            ])
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    #[Test]
    public function it_allows_password_login_when_user_has_passkeys_and_enforcement_disabled()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        $passkey = Mockery::mock(Passkey::class);
        $passkey->shouldReceive('id')->andReturn('passkey-1');
        $user->setPasskeys(collect([$passkey]));

        config(['statamic.webauthn.allow_password_login_with_passkey' => true]);

        $this
            ->post('/!/auth/login', [
                'email' => 'test@example.com',
                'password' => 'secret',
            ])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_gets_passkey_login_options()
    {
        $response = $this->get(route('statamic.passkeys.options'));

        $response->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('challenge', $data);
        $this->assertArrayHasKey('userVerification', $data);
        $this->assertEquals('required', $data['userVerification']);
    }

    #[Test]
    public function it_logs_in_with_passkey()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andReturnTrue();

        $this
            ->postJson(route('statamic.passkeys.login'))
            ->assertOk()
            ->assertJson(['redirect' => '/']);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_redirects_to_provided_url_after_passkey_login()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andReturnTrue();

        $this
            ->postJson(route('statamic.passkeys.login'), ['redirect' => '/dashboard'])
            ->assertOk()
            ->assertJson(['redirect' => '/dashboard']);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_does_not_redirect_to_external_url_after_passkey_login()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andReturnTrue();

        $this
            ->postJson(route('statamic.passkeys.login'), ['redirect' => 'https://evil.com'])
            ->assertOk()
            ->assertJson(['redirect' => '/']);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_fails_passkey_login_when_validation_fails()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andThrow(new \Exception('Invalid'));

        $this
            ->postJson(route('statamic.passkeys.login'))
            ->assertStatus(500);

        $this->assertGuest();
    }
}
