<?php

namespace Tests\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    #[Test]
    public function it_can_get_status_of_elevated_session()
    {
        $this->freezeSecond();

        $this
            ->session([
                "statamic_elevated_session_{$this->user->id}" => now()->addMinutes(5)->timestamp,
            ])
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => true,
                'time_remaining' => 300, // 5 minutes in seconds
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
                'time_remaining' => 0,
            ]);
    }

    #[Test]
    public function it_can_get_status_of_elevated_session_when_session_has_expired()
    {
        $this
            ->session([
                "statamic_elevated_session_{$this->user->id}" => now()->subMinutes(5)->timestamp,
            ])
            ->actingAs($this->user)
            ->get('/cp/elevated-session')
            ->assertOk()
            ->assertJson([
                'elevated' => false,
                'time_remaining' => 0,
            ]);
    }

    #[Test]
    public function it_can_start_elevated_session()
    {
        $this->freezeTime();

        $this
            ->actingAs($this->user)
            ->post('/cp/elevated-session', ['password' => 'secret'])
            ->assertOk()
            ->assertJsonStructure(['elevated', 'time_remaining'])
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

        $request = new Request();
        $request->setUserResolver(fn () => $this->user);

        $middleware = (new RequireElevatedSession)->handle($request, function () {
            return 'bar';
        });

        $this->assertEquals('bar', $middleware);
    }

    #[Test]
    public function middleware_denies_request_when_elevated_session_has_expired()
    {
        $this->actingAs($this->user);

        $this->session([
            "statamic_elevated_session_{$this->user->id}" => now()->subMinutes(5)->timestamp,
        ]);

        $request = new Request();
        $request->setUserResolver(fn () => $this->user);

        $middleware = (new RequireElevatedSession)->handle($request, function () {
            return 'bar';
        });

        $this->assertInstanceOf(JsonResponse::class, $middleware);
        $this->assertEquals(403, $middleware->getStatusCode());
        $this->assertEquals(['error' => 'Requires an elevated session.'], $middleware->getData(true));
    }
}
