<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\Passwords\TokenRepository;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TokenRepositoryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function repository(string $broker): TokenRepository
    {
        $broker = config('statamic.users.passwords.'.$broker);

        if (is_array($broker)) {
            $broker = $broker['cp'];
        }

        return Password::broker($broker)->getRepository();
    }

    #[Test]
    public function it_finds_email_by_token()
    {
        $user = tap(User::make()->email('san@holo.com')->password('secret'))->save();

        $token = Password::broker(config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS))->createToken($user);

        $this->assertSame('san@holo.com', $this->repository(PasswordReset::BROKER_RESETS)->findEmailByToken($token));
    }

    #[Test]
    public function it_returns_null_for_invalid_token()
    {
        $this->assertNull($this->repository(PasswordReset::BROKER_RESETS)->findEmailByToken('invalid-token'));
    }

    #[Test]
    public function it_returns_null_for_expired_token()
    {
        $user = tap(User::make()->email('san@holo.com')->password('secret'))->save();

        $repository = $this->repository(PasswordReset::BROKER_RESETS);
        $token = Password::broker(config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS))->createToken($user);

        $this->travel(2)->hours();

        $this->assertNull($repository->findEmailByToken($token));
    }
}
