<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\UserRegistered;
use Statamic\Events\UserRegistering;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function events_dispatched_when_user_registered()
    {
        Event::fake();

        $this
            ->post(route('statamic.register'), ['email' => 'foo@bar.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertRedirect();

        Event::assertDispatched(UserRegistering::class);
        Event::assertDispatched(UserRegistered::class);
    }

    #[Test]
    public function user_not_saved_when_user_registration_returns_false()
    {
        Event::fake([UserRegistered::class]);

        Event::listen(UserRegistering::class, function () {
            return false;
        });

        $this
            ->post(route('statamic.register'), ['email' => 'foo@bar.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertRedirect();

        $this->assertNull(User::findByEmail('foo@bar.com'));
        Event::assertNotDispatched(UserRegistered::class);
    }
}
