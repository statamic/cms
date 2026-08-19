<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CpForgotPasswordTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function tearDown(): void
    {
        // Prevent leaking into other tests
        PasswordReset::resetFormUrl(null);
        PasswordReset::resetFormRoute(null);
        PasswordReset::redirectAfterReset(null);

        parent::tearDown();
    }

    #[Test]
    public function it_returns_generic_success_for_non_existent_user_to_prevent_enumeration()
    {
        Notification::fake();

        $this
            ->post(cp_route('password.email'), [
                'email' => 'nobody@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('user.forgot_password.success', __(Password::RESET_LINK_SENT))
            ->assertSessionHas('status', __(Password::RESET_LINK_SENT));

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_returns_generic_success_for_throttled_user_to_prevent_enumeration()
    {
        Notification::fake();

        $throttled = new class
        {
            public function sendResetLink()
            {
                return Password::RESET_THROTTLED;
            }
        };

        Password::shouldReceive('broker')->andReturn($throttled);

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post(cp_route('password.email'), [
                'email' => 'san@holo.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('user.forgot_password.success', __(Password::RESET_LINK_SENT))
            ->assertSessionHas('status', __(Password::RESET_LINK_SENT));

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_still_errors_on_invalid_email_format()
    {
        $this
            ->post(cp_route('password.email'), [
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('email', null, 'user.forgot_password');
    }
}
