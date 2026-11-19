<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\UserRegistered;
use Statamic\Events\UserRegistering;
use Statamic\Facades\User;
use Tests\Auth\UnsafeEmailPayloads;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, UnsafeEmailPayloads;

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

    #[Test]
    #[DataProvider('unsafeEmailProvider')]
    public function it_rejects_emails_that_are_unsafe_as_file_paths($email)
    {
        $before = $this->filesystemSnapshot();

        $this
            ->post(route('statamic.register'), ['email' => $email, 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors('email', null, 'user.register');

        $this->assertSame($before, $this->filesystemSnapshot());
    }

    #[Test]
    public function it_does_not_overwrite_an_existing_user_when_registering_with_a_leading_slash()
    {
        $victim = tap(User::make()->id('victim-id')->email('victim@x.com')->makeSuper())->save();
        $contents = file_get_contents($victim->path());

        $this
            ->post(route('statamic.register'), ['email' => '/victim@x.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors('email', null, 'user.register');

        $this->assertSame($contents, file_get_contents($victim->path()));
    }

    #[Test]
    public function it_returns_a_404_and_creates_no_user_when_pro_is_disabled()
    {
        config(['statamic.editions.pro' => false]);

        $this
            ->post(route('statamic.register'), ['email' => 'foo@bar.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertNotFound();

        $this->assertCount(0, User::all());
        $this->assertGuest();
    }

    #[Test]
    public function it_returns_a_404_rather_than_validation_errors_when_pro_is_disabled()
    {
        config(['statamic.editions.pro' => false]);

        $this->post(route('statamic.register'), ['email' => 'not-an-email'])->assertNotFound();
        $this->postJson(route('statamic.register'), [])->assertNotFound();

        $this->assertCount(0, User::all());
    }

    #[Test]
    public function it_registers_a_user_when_pro_is_enabled()
    {
        config(['statamic.editions.pro' => true]);

        $this
            ->post(route('statamic.register'), ['email' => 'foo@bar.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertRedirect();

        $this->assertCount(1, User::all());
    }
}
