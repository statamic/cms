<?php

namespace Tests\Http\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StartSessionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function hitting_the_session_timeout_route_does_not_extend_the_session()
    {
        $this->freezeTime();
        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)->get(cp_route('elevated-session.status'))->assertOk();
        $this->assertEquals(now()->timestamp, session('last_activity'));

        $this->travel(30)->seconds();

        $this->actingAs($user)->get(cp_route('session.timeout'))->assertOk();
        $this->assertNotEquals(now()->timestamp, session('last_activity'));
    }

    #[Test]
    public function hitting_the_token_route_does_not_extend_the_session()
    {
        $this->freezeTime();
        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)->get(cp_route('elevated-session.status'))->assertOk();
        $this->assertEquals(now()->timestamp, session('last_activity'));

        $this->travel(30)->seconds();

        $this->actingAs($user)->get(cp_route('token'))->assertOk();
        $this->assertNotEquals(now()->timestamp, session('last_activity'));
    }

    #[Test]
    public function hitting_a_normal_cp_route_extends_the_session()
    {
        $this->freezeTime();
        $user = tap(User::make()->makeSuper())->save();

        $this->actingAs($user)->get(cp_route('elevated-session.status'))->assertOk();
        $this->assertEquals(now()->timestamp, session('last_activity'));

        $this->travel(30)->seconds();

        $this->actingAs($user)->get(cp_route('elevated-session.status'))->assertOk();
        $this->assertEquals(now()->timestamp, session('last_activity'));
    }
}
