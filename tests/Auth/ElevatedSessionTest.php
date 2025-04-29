<?php

namespace Tests\Auth;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\RequireElevatedSession;
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

        $this->freezeSecond();

        $this
            ->withElevatedSession(now()->subMinutes(5))
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => true,
                'expiry' => now()->addMinutes(10)->timestamp,
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
            ]);
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
}
