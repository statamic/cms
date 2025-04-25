<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\RequireElevatedSession;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ElevatedSessionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::make()->makeSuper()->password('secret');
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

    #[Test]
    public function it_can_get_status_of_elevated_session()
    {
        $this->freezeSecond();

        $this
            ->session([
                "statamic_elevated_session_{$this->user->id}" => $exp = now()->addMinutes(5)->timestamp,
            ])
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => true,
                'expiry' => $exp,
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
        $this
            ->session([
                "statamic_elevated_session_{$this->user->id}" => $exp = now()->subMinutes(5)->timestamp,
            ])
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => false,
                'expiry' => $exp,
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
            ->assertSessionHas("statamic_elevated_session_{$this->user->id}", now()->addMinutes(15)->timestamp);
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
            ->assertSessionHas("statamic_elevated_session_{$this->user->id}", now()->addMinutes(15)->timestamp);
    }

    #[Test]
    public function it_cannot_start_elevated_session_with_incorrect_password()
    {
        $this
            ->actingAs($this->user)
            ->post('/cp/elevated-session', ['password' => 'incorrect-password'])
            ->assertSessionHasErrors('password')
            ->assertSessionMissing("statamic_elevated_session_{$this->user->id}");
    }

    #[Test]
    public function middleware_allows_request()
    {
        $this->actingAs($this->user);

        $this->session([
            "statamic_elevated_session_{$this->user->id}" => now()->addMinutes(5)->timestamp,
        ]);

        $this->get('/requires-elevated-session')
            ->assertOk()
            ->assertSee('ok');
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired()
    {
        $this->actingAs($this->user);

        $this->session([
            "statamic_elevated_session_{$this->user->id}" => now()->subMinutes(5)->timestamp,
        ]);

        $this->get('/requires-elevated-session')
            ->assertRedirect('/cp/auth/confirm-password');
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired_via_json()
    {
        $this->actingAs($this->user);

        $this->session([
            "statamic_elevated_session_{$this->user->id}" => now()->subMinutes(5)->timestamp,
        ]);

        $this->getJson('/requires-elevated-session')
            ->assertStatus(403)
            ->assertJson([
                'message' => __('Requires an elevated session.'),
            ]);
    }
}
