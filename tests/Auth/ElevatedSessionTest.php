<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Statamic\Http\Middleware;
use Statamic\Notifications\ElevatedSessionVerificationCode;
use Tests\ElevatesSessions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class ElevatedSessionTest extends TestCase
{
    use ElevatesSessions;
    use PreventSavingStacheItemsToDisk;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();

        $this->user = User::make()->email('foo@bar.com')->makeSuper()->password('secret');
        $this->user->save();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/requires-elevated-session', function () {
                return 'ok';
            })->middleware(Middleware\RequireElevatedSession::class);

            Route::post('/requires-elevated-session', function () {
                return 'ok';
            })->middleware(Middleware\RequireElevatedSession::class);

            Route::get('/cp/requires-elevated-session', function () {
                return 'ok';
            })->middleware(Middleware\CP\RequireElevatedSession::class);

            Route::post('/cp/requires-elevated-session', function () {
                return 'ok';
            })->middleware(Middleware\CP\RequireElevatedSession::class);
        });
    }

    #[Test]
    public function it_can_get_status_of_elevated_session()
    {
        config(['statamic.users.elevated_session_duration' => 15]);

        $this
            ->withElevatedSession(now()->subMinutes(5))
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => true,
                'expiry' => now()->addMinutes(10)->timestamp,
                'method' => 'password_confirmation',
            ]);
    }

    #[Test]
    public function it_can_get_status_of_elevated_session_when_session_key_does_not_exist()
    {
        $this
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => false,
                'expiry' => null,
                'method' => 'password_confirmation',
            ]);
    }

    #[Test]
    public function it_can_get_status_of_elevated_session_when_session_has_expired()
    {
        config(['statamic.users.elevated_session_duration' => 15]);

        $this
            ->withElevatedSession(now()->subMinutes(20))
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => false,
                'expiry' => now()->subMinutes(5)->timestamp,
                'method' => 'password_confirmation',
            ]);
    }

    #[Test]
    public function it_can_get_status_of_elevated_session_when_session_has_expired_and_user_doesnt_have_a_password()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');
        $user = tap(User::make()->email('foo@bar.com')->makeSuper())->save();
        config(['statamic.users.elevated_session_duration' => 15]);

        $this
            ->withElevatedSession(now()->subMinutes(20))
            ->actingAs($user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => false,
                'expiry' => now()->subMinutes(5)->timestamp,
                'method' => 'verification_code',
            ])
            ->assertSessionHas('statamic_elevated_session_verification_code', [
                'code' => 'abc',
                'generated_at' => now()->timestamp,
            ]);

        Notification::assertSentTo($user, ElevatedSessionVerificationCode::class, function ($notification, $channels) {
            return $notification->verificationCode === 'abc';
        });
    }

    #[Test]
    public function when_getting_status_for_user_without_password_it_only_sends_notification_once()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');
        $user = tap(User::make()->email('foo@bar.com')->makeSuper())->save();
        config(['statamic.users.elevated_session_duration' => 15]);

        $request = function () use ($user) {
            return $this
                ->withElevatedSession(now()->subMinutes(20))
                ->actingAs($user)
                ->get('/cp/elevated-session');
        };

        $request(); // Sent.
        $request(); // Within 5-minute window. Not sent.
        $this->travel(30)->seconds();
        $request(); // Still within 5-minute window. Not sent.
        $this->travel(5)->minute();
        $request(); // Outside 5 minutes. Sent.

        Notification::assertCount(2);
    }

    #[Test]
    public function it_can_start_elevated_session()
    {
        redirect()->setIntendedUrl('/cp/target-url');

        $this
            ->actingAs($this->user)
            ->post('/cp/elevated-session', ['password' => 'secret'])
            ->assertRedirect('/cp/target-url')
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function it_can_start_elevated_session_via_json()
    {
        $this
            ->actingAs($this->user)
            ->postJson('/cp/elevated-session', ['password' => 'secret'])
            ->assertOk()
            ->assertJsonStructure(['elevated', 'expiry'])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function starting_elevated_session_clears_stored_verification_code()
    {
        $user = tap(User::make()->email('foo@bar.com')->makeSuper())->save();

        session()->put('statamic_elevated_session_verification_code', [
            'code' => 'abc',
            'generated_at' => now()->timestamp,
        ]);

        $this
            ->actingAs($user)
            ->post('/cp/elevated-session', ['verification_code' => 'abc'])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp)
            ->assertSessionMissing('statamic_elevated_session_verification_code');
    }

    #[Test]
    public function it_cannot_start_elevated_session_with_incorrect_password()
    {
        $this
            ->actingAs($this->user)
            ->post('/cp/elevated-session', ['password' => 'incorrect-password'])
            ->assertSessionHasErrors('password')
            ->assertSessionMissing('statamic_elevated_session');
    }

    #[Test]
    #[DataProvider('invalidPasswordPayloads')]
    public function it_rejects_invalid_password_payloads(array $payload): void
    {
        $this
            ->actingAs($this->user)
            ->postJson('/cp/elevated-session', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('password')
            ->assertSessionMissing('statamic_elevated_session');
    }

    public static function invalidPasswordPayloads(): array
    {
        return [
            'no fields' => [[]],
            'string zero' => [['password' => '0']],
            'integer zero' => [['password' => 0]],
            'false' => [['password' => false]],
            'empty string' => [['password' => '']],
            'null' => [['password' => null]],
        ];
    }

    #[Test]
    #[DataProvider('invalidVerificationCodePayloads')]
    public function it_rejects_invalid_verification_code_payloads(array $payload): void
    {
        $this
            ->actingAs($this->user)
            ->postJson('/cp/elevated-session', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('verification_code')
            ->assertSessionMissing('statamic_elevated_session');
    }

    public static function invalidVerificationCodePayloads(): array
    {
        return [
            'no fields' => [[]],
            'string zero' => [['verification_code' => '0']],
            'integer zero' => [['verification_code' => 0]],
            'false' => [['verification_code' => false]],
            'empty string' => [['verification_code' => '']],
            'null' => [['verification_code' => null]],
        ];
    }

    #[Test]
    #[DataProvider('invalidPasskeyPayloads')]
    public function it_handles_invalid_passkey_payloads(array $payload, bool $expectsValidationError): void
    {
        if ($expectsValidationError) {
            $this
                ->actingAs($this->user)
                ->postJson('/cp/elevated-session', $payload)
                ->assertStatus(422)
                ->assertJsonValidationErrors('id')
                ->assertSessionMissing('statamic_elevated_session');

            return;
        }

        WebAuthn::shouldReceive('validateAssertion')->once()->andThrow(new \RuntimeException('Invalid assertion'));

        $this
            ->actingAs($this->user)
            ->postJson('/cp/elevated-session', array_merge($payload, ['rawId' => 'raw-id', 'response' => [], 'type' => 'public-key']))
            ->assertStatus(500)
            ->assertSessionMissing('statamic_elevated_session');
    }

    public static function invalidPasskeyPayloads(): array
    {
        return [
            'no fields' => [[], true],
            'string zero' => [['id' => '0'], false],
            'integer zero' => [['id' => 0], false],
            'false' => [['id' => false], false],
            'empty string' => [['id' => ''], true],
            'null' => [['id' => null], true],
        ];
    }

    #[Test]
    public function middleware_allows_request()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession()
            ->get('/cp/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->get('/cp/requires-elevated-session')
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function middleware_uses_referer_as_intended_url_for_post_requests()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->post('/cp/requires-elevated-session', [], ['referer' => 'http://localhost/cp/some-form'])
            ->assertRedirect('/cp/auth/confirm-password')
            ->assertSessionHas('url.intended', 'http://localhost/cp/some-form');
    }

    #[Test]
    public function middleware_falls_back_to_full_url_when_referer_is_external_for_post_requests()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->post('/cp/requires-elevated-session', [], ['referer' => 'https://evil.example.com/form'])
            ->assertRedirect('/cp/auth/confirm-password')
            ->assertSessionHas('url.intended', 'http://localhost/cp/requires-elevated-session');
    }

    #[Test]
    public function middleware_falls_back_to_full_url_when_referer_is_missing_for_post_requests()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->post('/cp/requires-elevated-session')
            ->assertRedirect('/cp/auth/confirm-password')
            ->assertSessionHas('url.intended', 'http://localhost/cp/requires-elevated-session');
    }

    #[Test]
    public function middleware_uses_full_url_as_intended_url_for_get_requests()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->get('/cp/requires-elevated-session', ['referer' => 'http://localhost/cp/some-form'])
            ->assertRedirect('/cp/auth/confirm-password')
            ->assertSessionHas('url.intended', 'http://localhost/cp/requires-elevated-session');
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired_via_json()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->getJson('/cp/requires-elevated-session')
            ->assertStatus(403)
            ->assertJson(['message' => __('Requires an elevated session.')]);
    }

    #[Test]
    public function middleware_does_not_require_elevated_session_when_elevated_session_is_disabled()
    {
        config(['statamic.users.elevated_sessions_enabled' => false]);

        $this->actingAs($this->user);

        $this
            ->get('/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function middleware_does_not_require_elevated_session_when_elevated_session_is_disabled_even_if_session_expired()
    {
        config(['statamic.users.elevated_sessions_enabled' => false]);

        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->get('/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function middleware_does_not_require_elevated_session_when_elevated_session_is_disabled_via_json()
    {
        config(['statamic.users.elevated_sessions_enabled' => false]);

        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->getJson('/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function the_session_is_elevated_upon_login()
    {
        $this
            ->post(cp_route('login'), [
                'email' => 'foo@bar.com',
                'password' => 'secret',
            ])
            ->assertRedirectToRoute('statamic.cp.index');

        $this
            ->get('/cp/requires-elevated-session')
            ->assertOk();
    }

    #[Test]
    public function the_session_is_elevated_upon_login_with_oauth()
    {
        $this->markTestIncomplete('Implementation is done but is missing a test.');
    }

    #[Test]
    public function the_verification_code_will_be_sent_for_passwordless_user_when_loading_the_form()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $this
            ->actingAs($user = tap(User::make()->email('foo@bar.com')->makeSuper())->save())
            ->get(cp_route('confirm-password'))
            ->assertSessionHas('statamic_elevated_session_verification_code', [
                'code' => 'abc',
                'generated_at' => now()->timestamp,
            ]);

        Notification::assertSentTo($user, ElevatedSessionVerificationCode::class, function ($notification, $channels) {
            return $notification->verificationCode === 'abc';
        });
    }

    #[Test]
    public function the_verification_code_will_be_sent_for_passwordless_user_when_loading_the_form_once()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');
        $user = tap(User::make()->email('foo@bar.com')->makeSuper())->save();

        $request = function () use ($user) {
            return $this
                ->actingAs($user)
                ->get(cp_route('confirm-password'));
        };

        $request(); // Sent.
        $request(); // Within 5-minute window. Not sent.
        $this->travel(30)->seconds();
        $request(); // Still within 5-minute window. Not sent.
        $this->travel(5)->minute();
        $request(); // Outside 5 minutes. Sent.

        Notification::assertCount(2);
    }

    #[Test]
    public function the_verification_code_can_be_resent()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $this
            ->actingAs($user = User::make()->email('foo@bar.com')->makeSuper())
            ->from('/original')
            ->get(cp_route('elevated-session.resend-code'))
            ->assertRedirect('/original')
            ->assertSessionHas('status')
            ->assertSessionHas('statamic_elevated_session_verification_code', [
                'code' => 'abc',
                'generated_at' => now()->timestamp,
            ]);

        Notification::assertSentTo($user, ElevatedSessionVerificationCode::class, function ($notification, $channels) {
            return $notification->verificationCode === 'abc';
        });
    }

    #[Test]
    public function resending_code_is_rate_limited()
    {
        Notification::fake();
        $user = User::make()->email('foo@bar.com')->makeSuper();

        $request = function () use ($user) {
            return $this
                ->actingAs($user)
                ->from('/original')
                ->get(cp_route('elevated-session.resend-code'));
        };

        $request()->assertRedirect('/original')->assertSessionHas('status');
        $request()->assertRedirect('/original')->assertSessionHas('error', 'Try again in a minute.');
        $this->travel(30)->seconds();
        $request()->assertRedirect('/original')->assertSessionHas('error', 'Try again in a minute.');
        $this->travel(1)->minute();
        $request()->assertRedirect('/original')->assertSessionHas('status');

        Notification::assertCount(2);
    }

    #[Test]
    public function frontend_resending_code_is_rate_limited()
    {
        Notification::fake();
        $user = User::make()->email('foo@bar.com')->makeSuper();

        $request = function () use ($user) {
            return $this
                ->actingAs($user)
                ->from('/original')
                ->get(route('statamic.elevated-session.resend-code'));
        };

        $request()->assertRedirect('/original')->assertSessionHas('status');
        $request()->assertRedirect('/original')->assertSessionHas('error', 'Try again in a minute.');
        $this->travel(30)->seconds();
        $request()->assertRedirect('/original')->assertSessionHas('error', 'Try again in a minute.');
        $this->travel(1)->minute();
        $request()->assertRedirect('/original')->assertSessionHas('status');

        Notification::assertCount(2);
    }

    #[Test]
    public function the_verification_code_will_not_be_sent_if_the_user_has_a_password()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $this
            ->actingAs($this->user)
            ->from('/original')
            ->get(cp_route('elevated-session.resend-code'))
            ->assertSessionHasErrors(['method' => 'Resend code is only available for verification code method.'])
            ->assertSessionMissing('statamic_elevated_session_verification_code');

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_returns_passkey_method_when_config_is_false_and_user_has_passkeys()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockCollection = collect(['passkey-123' => $mockPasskey]);

        $user = tap(User::make()->email('foo@bar.com')->makeSuper()->password('secret'))->save();

        // Use reflection to set the passkeys property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('passkeys');
        $property->setValue($user, $mockCollection);

        $this->assertEquals('passkey', $user->getElevatedSessionMethod());
    }

    #[Test]
    public function it_returns_password_confirmation_method_when_config_is_true_even_with_passkeys()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => true]);

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockCollection = collect(['passkey-123' => $mockPasskey]);

        $user = tap(User::make()->email('foo@bar.com')->makeSuper()->password('secret'))->save();

        // Use reflection to set the passkeys property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('passkeys');
        $property->setValue($user, $mockCollection);

        $this->assertEquals('password_confirmation', $user->getElevatedSessionMethod());
    }

    #[Test]
    public function it_returns_password_confirmation_when_user_has_no_passkeys_regardless_of_config()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $this->assertEquals('password_confirmation', $this->user->getElevatedSessionMethod());
    }

    #[Test]
    public function it_returns_verification_code_when_no_password_and_no_passkeys()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $user = tap(User::make()->email('foo@bar.com')->makeSuper())->save();

        $this->assertEquals('verification_code', $user->getElevatedSessionMethod());
    }

    #[Test]
    public function it_can_get_passkey_options_for_elevated_session()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockCollection = collect(['passkey-123' => $mockPasskey]);

        $user = $this->user;

        // Use reflection to set the passkeys property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('passkeys');
        $property->setValue($user, $mockCollection);

        $response = $this
            ->actingAs($user)
            ->get(cp_route('elevated-session.passkey-options'))
            ->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('challenge', $data);
        $this->assertNotNull(session('webauthn.challenge'));
    }

    #[Test]
    public function it_can_start_elevated_session_with_passkey()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockCollection = collect(['passkey-123' => $mockPasskey]);

        $user = $this->user;

        // Use reflection to set the passkeys property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('passkeys');
        $property->setValue($user, $mockCollection);

        $credentials = [
            'id' => 'credential-id',
            'rawId' => 'raw-id',
            'response' => [],
            'type' => 'public-key',
        ];

        WebAuthn::shouldReceive('validateAssertion')
            ->once()
            ->with($user, $credentials)
            ->andReturnTrue();

        $this
            ->actingAs($user)
            ->postJson('/cp/elevated-session', $credentials)
            ->assertOk()
            ->assertJsonStructure(['elevated', 'expiry'])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function status_endpoint_returns_passkey_method()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockCollection = collect(['passkey-123' => $mockPasskey]);

        $user = $this->user;

        // Use reflection to set the passkeys property
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('passkeys');
        $property->setValue($user, $mockCollection);

        $this
            ->actingAs($user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => false,
                'expiry' => null,
                'method' => 'passkey',
            ]);
    }

    #[Test]
    public function frontend_middleware_allows_request()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession()
            ->get('/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function frontend_middleware_denies_request_when_elevated_session_has_expired()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->get('/requires-elevated-session')
            ->assertRedirect('/!/auth/confirm-password');
    }

    #[Test]
    public function frontend_middleware_denies_request_when_elevated_session_has_expired_via_json()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->getJson('/requires-elevated-session')
            ->assertStatus(403)
            ->assertJson(['message' => __('Requires an elevated session.')]);
    }

    #[Test]
    public function frontend_elevated_session_redirects_to_custom_url_when_configured()
    {
        config(['statamic.users.elevated_sessions_url' => '/custom-elevated-session']);

        $this
            ->actingAs($this->user)
            ->get('/!/auth/confirm-password')
            ->assertRedirect('/custom-elevated-session');
    }

    #[Test]
    public function frontend_elevated_session_shows_inertia_page_when_no_custom_url()
    {
        config(['statamic.users.elevated_sessions_url' => null]);

        $this
            ->actingAs($this->user)
            ->get('/!/auth/confirm-password')
            ->assertInertia(fn ($page) => $page
                ->component('auth/ConfirmPassword')
                ->where('outside', true)
                ->has('method')
                ->has('allowPasskey')
                ->has('submitUrl')
                ->has('resendUrl')
                ->has('passkeyOptionsUrl')
            );
    }

    #[Test]
    public function frontend_can_confirm_elevated_session_with_password()
    {
        redirect()->setIntendedUrl('/target-url');

        $this
            ->actingAs($this->user)
            ->post('/!/auth/elevated-session', ['password' => 'secret'])
            ->assertRedirect('/target-url')
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function frontend_can_confirm_elevated_session_via_json()
    {
        $this
            ->actingAs($this->user)
            ->postJson('/!/auth/elevated-session', ['password' => 'secret'])
            ->assertOk()
            ->assertJsonStructure(['elevated', 'expiry', 'redirect'])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function frontend_can_confirm_elevated_session_via_inertia()
    {
        redirect()->setIntendedUrl('/target-url');

        $this
            ->actingAs($this->user)
            ->post('/!/auth/elevated-session', ['password' => 'secret'], ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'http://localhost/target-url')
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function frontend_cannot_confirm_elevated_session_with_incorrect_password()
    {
        $this
            ->actingAs($this->user)
            ->post('/!/auth/elevated-session', ['password' => 'wrong'])
            ->assertSessionHasErrors(['password'], null, 'user.elevated_session')
            ->assertSessionMissing('statamic_elevated_session');
    }

    #[Test]
    public function frontend_inertia_request_puts_errors_in_default_bag()
    {
        $this
            ->actingAs($this->user)
            ->post('/!/auth/elevated-session', ['password' => 'wrong'], ['X-Inertia' => 'true'])
            ->assertSessionHasErrors(['password'])
            ->assertSessionMissing('statamic_elevated_session');
    }

    #[Test]
    public function frontend_verification_code_will_be_sent_for_passwordless_user_when_loading_the_form()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');
        config(['statamic.users.elevated_sessions_url' => null]);

        $this
            ->actingAs($user = tap(User::make()->email('foo@bar.com')->makeSuper())->save())
            ->get('/!/auth/confirm-password')
            ->assertSessionHas('statamic_elevated_session_verification_code', [
                'code' => 'abc',
                'generated_at' => now()->timestamp,
            ]);

        Notification::assertSentTo($user, ElevatedSessionVerificationCode::class, function ($notification) {
            return $notification->verificationCode === 'abc';
        });
    }

    #[Test]
    public function frontend_verification_code_can_be_resent()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $this
            ->actingAs($user = User::make()->email('foo@bar.com')->makeSuper())
            ->from('/original')
            ->get('/!/auth/elevated-session/resend-code')
            ->assertRedirect('/original')
            ->assertSessionHas('status')
            ->assertSessionHas('statamic_elevated_session_verification_code', [
                'code' => 'abc',
                'generated_at' => now()->timestamp,
            ]);

        Notification::assertSentTo($user, ElevatedSessionVerificationCode::class, function ($notification) {
            return $notification->verificationCode === 'abc';
        });
    }

    #[Test]
    public function the_session_is_elevated_upon_frontend_login()
    {
        $this
            ->post('/!/auth/login', [
                'email' => 'foo@bar.com',
                'password' => 'secret',
            ])
            ->assertRedirect('/');

        $this
            ->get('/requires-elevated-session')
            ->assertOk();
    }
}
