<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public static function resetPasswordProvider()
    {
        return [
            'cp' => ['cp'],
            'web' => ['web'],
        ];
    }

    private function resetUrl($type)
    {
        return match ($type) {
            'cp' => cp_route('password.reset.action'),
            'web' => route('statamic.password.reset.action'),
        };
    }

    private function defaultRedirectUrl($type)
    {
        return match ($type) {
            'cp' => cp_route('login'),
            'web' => route('statamic.site'),
        };
    }

    private function createUser()
    {
        return tap(User::make()->makeSuper()->email('san@holo.com')->password('secret'))->save();
    }

    private function createToken($user, $type)
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        if (is_array($broker)) {
            $broker = $broker[$type];
        }

        return Password::broker($broker)->createToken($user);
    }

    #[Test]
    #[DataProvider('resetPasswordProvider')]
    public function it_resets_the_password_and_user_is_not_authenticated($type)
    {
        $user = $this->createUser();
        $token = $this->createToken($user, $type);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => 'san@holo.com',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHas('status')
            ->assertRedirect($this->defaultRedirectUrl($type));

        $this->assertGuest();
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }
}
