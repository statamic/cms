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

    private function createUser()
    {
        return tap(User::make()->id('test-user')->email('test@example.com')->password('secret'))->save();
    }

    #[Test]
    public function disabled_frontend_authentication_does_not_log_out_authenticated_users(): void
    {
        $user = User::make()->email('test@example.com')->save();

        config(['statamic.users.frontend_auth_enabled' => false]);

        $this->actingAs($user)->get('/!/auth/logout')->assertNotFound();

        $this->assertAuthenticatedAs($user);
    }
}
