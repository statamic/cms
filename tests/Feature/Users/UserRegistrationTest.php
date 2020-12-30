<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Facades\Event;
use Statamic\Events\UserRegistered;
use Statamic\Events\UserRegistering;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function user_registered_event_dispatched_when_user_registered()
    {
        Event::fake();

        $this
            ->post(route('statamic.register'), ['email'=>'foo@bar.com', 'password'=>'password', 'password_confirmation'=>'password'])
            ->assertRedirect();

        Event::assertDispatched(UserRegistered::class);
    }

    /** @test */
    public function user_registering_event_dispatched_when_user_registered()
    {
        Event::fake();

        $this
            ->post(route('statamic.register'), ['email'=>'foo@bar.com', 'password'=>'password', 'password_confirmation'=>'password'])
            ->assertRedirect();

        Event::assertDispatched(UserRegistering::class);
    }

    /** @test */
    public function user_not_saved_when_user_registration_returns_false()
    {
        // need to register the "legacy" way so it works on L6 & L7
        Event::listen(UserRegistering::class, function () {
            return false;
        });

        $this
            ->post(route('statamic.register'), ['email'=>'foo@bar.com', 'password'=>'password', 'password_confirmation'=>'password'])
            ->assertRedirect();

        $this->assertNull(User::findByEmail('foo@bar.com'));
    }
}
