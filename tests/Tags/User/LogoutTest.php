<?php

namespace Tests\Tags\User;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_can_logout()
    {
        $this
            ->actingAs($this->createUser())
            ->get(route('statamic.logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function it_redirects_to_local_url()
    {
        $this
            ->actingAs($this->createUser())
            ->get(route('statamic.logout').'?redirect=/home')
            ->assertRedirect('/home');

        $this->assertGuest();
    }

    #[Test]
    public function it_does_not_redirect_to_external_url()
    {
        $this
            ->actingAs($this->createUser())
            ->get(route('statamic.logout').'?redirect=https://evil.com')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function it_can_logout_a_specific_guard()
    {
        config()->set('auth.guards.statamic', config('auth.guards.web'));

        $this
            ->actingAs($this->createUser(), 'statamic')
            ->actingAs(User::make()->id('web-user')->email('web@example.com')->save(), 'web')
            ->get(route('statamic.logout', ['guard' => 'statamic']))
            ->assertRedirect('/');

        $this->assertGuest('statamic');
        $this->assertAuthenticated('web');
    }

    #[Test]
    #[DataProvider('invalidGuardProvider')]
    public function it_does_not_logout_an_invalid_guard(string $guard)
    {
        config()->set('auth.guards.api', ['driver' => 'token', 'provider' => 'users']);

        $this
            ->actingAs($this->createUser())
            ->get(route('statamic.logout', ['guard' => $guard]))
            ->assertNotFound();

        $this->assertAuthenticated();
    }

    public static function invalidGuardProvider(): array
    {
        return [
            'unknown guard' => ['nope'],
            'non-session guard' => ['api'],
        ];
    }

    #[Test]
    public function it_does_not_logout_an_array_of_guards()
    {
        $this
            ->actingAs($this->createUser())
            ->get(route('statamic.logout', ['guard' => ['web']]))
            ->assertNotFound();

        $this->assertAuthenticated();
    }

    #[Test]
    public function it_ignores_the_guard_param_on_the_cp_logout_route()
    {
        config()->set('auth.guards.statamic', config('auth.guards.web'));

        $this
            ->actingAs($this->createUser(), 'statamic')
            ->actingAs(User::make()->id('web-user')->email('web@example.com')->save(), 'web')
            ->get(cp_route('logout', ['guard' => 'statamic']))
            ->assertRedirect('/');

        $this->assertAuthenticated('statamic');
        $this->assertGuest('web');
    }

    private function createUser()
    {
        return tap(User::make()->id('test-user')->email('test@example.com')->password('secret'))->save();
    }
}
