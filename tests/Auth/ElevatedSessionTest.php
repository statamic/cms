<?php

namespace Tests\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\RequireElevatedSession;
use Statamic\Notifications\ElevatedSessionVerificationCode;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class ElevatedSessionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::make()->email('foo@bar.com')->makeSuper()->password('secret');
        $this->user->save();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/requires-elevated-session', function () {
                return 'ok';
            })->middleware(RequireElevatedSession::class);
        });
    }

    private function withElevatedSession(?Carbon $time = null)
    {
        return $this->session(['statamic_elevated_session' => ($time ?? now())->timestamp]);
    }

    #[Test]
    public function it_can_get_status_of_elevated_session()
    {
        config(['statamic.users.elevated_session_duration' => 15]);

        $this->freezeTime();

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
        $this->freezeTime();

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
        $this->freezeTime();
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
        $this->freezeTime();

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
        $this->freezeTime();

        $this
            ->actingAs($this->user)
            ->postJson('/cp/elevated-session', ['password' => 'secret'])
            ->assertOk()
            ->assertJsonStructure(['elevated', 'expiry'])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
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
    public function middleware_allows_request()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession()
            ->get('/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->get('/requires-elevated-session')
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired_via_json()
    {
        $this->actingAs($this->user);

        $this
            ->withElevatedSession(now()->subMinutes(16))
            ->getJson('/requires-elevated-session')
            ->assertStatus(403)
            ->assertJson(['message' => __('Requires an elevated session.')]);
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
            ->get('/requires-elevated-session')
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
        $this->freezeTime();
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
        $this->freezeTime();
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
        $this->freezeTime();
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $this
            ->actingAs($user = User::make()->email('foo@bar.com')->makeSuper())
            ->from('/original')
            ->get(cp_route('elevated-session.resend-code'))
            ->assertRedirect('/original')
            ->assertSessionHas('success')
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
        $this->freezeTime();
        Notification::fake();
        $user = User::make()->email('foo@bar.com')->makeSuper();

        $request = function () use ($user) {
            return $this
                ->actingAs($user)
                ->from('/original')
                ->get(cp_route('elevated-session.resend-code'));
        };

        $request()->assertRedirect('/original')->assertSessionHas('success');
        $request()->assertRedirect('/original')->assertSessionHas('error', 'Try again in a minute.');
        $this->travel(30)->seconds();
        $request()->assertRedirect('/original')->assertSessionHas('error', 'Try again in a minute.');
        $this->travel(1)->minute();
        $request()->assertRedirect('/original')->assertSessionHas('success');

        Notification::assertCount(2);
    }

    #[Test]
    public function the_verification_code_will_not_be_sent_if_the_user_has_a_password()
    {
        $this->freezeTime();
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $this
            ->actingAs($this->user)
            ->from('/original')
            ->get(cp_route('elevated-session.resend-code'))
            ->assertSessionHasErrors(['method' => 'Resend code is only available for verification code method'])
            ->assertSessionMissing('statamic_elevated_session_verification_code');

        Notification::assertNothingSent();
    }
}
