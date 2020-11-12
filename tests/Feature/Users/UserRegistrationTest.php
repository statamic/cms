<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Facades\Event;
use Statamic\Events\UserRegistered;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function events_dispatched_when_user_registered()
    {
        Event::fake();

        $this
            ->post(route('statamic.register'), ['email'=>'foo@bar.com', 'password'=>'password', 'password_confirmation'=>'password'])
            ->assertRedirect();

        Event::assertDispatched(UserRegistered::class);
    }
}
