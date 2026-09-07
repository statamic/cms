<?php

namespace Tests\Tags\User;

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
    public function it_does_not_logout_an_unknown_guard()
    {
        $this
            ->actingAs($this->createUser())
            ->get(route('statamic.logout', ['guard' => 'nope']))
            ->assertNotFound();

        $this->assertAuthenticated();
    }

    private function createUser()
    {
        return tap(User::make()->id('test-user')->email('test@example.com')->password('secret'))->save();
    }
}
