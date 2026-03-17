<?php

namespace Tests\Auth\WebAuthn;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Webauthn\PublicKeyCredentialSource;

trait PasskeyTests
{
    abstract protected function newPasskey(): Passkey;

    abstract protected function createTestCredential(string $id = 'test-credential-id-123'): PublicKeyCredentialSource;

    #[Test]
    public function it_gets_last_login()
    {
        $user = tap(User::make()->email('test@example.com')->data(['name' => 'John Smith']))->save();
        $credential = $this->createTestCredential();
        $lastLogin = Carbon::create(2024, 1, 15, 10, 30, 0);

        $passkey = $this->newPasskey()
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential)
            ->setLastLogin($lastLogin);

        $this->assertInstanceOf(Carbon::class, $passkey->lastLogin());
        $this->assertEquals('2024-01-15 10:30:00', $passkey->lastLogin()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_handles_null_last_login()
    {
        $user = tap(User::make()->email('test@example.com')->data(['name' => 'John Smith']))->save();
        $credential = $this->createTestCredential();

        $passkey = $this->newPasskey()
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $this->assertNull($passkey->lastLogin());
    }

    #[Test]
    public function it_sets_last_login_from_timestamp()
    {
        $user = tap(User::make()->email('test@example.com')->data(['name' => 'John Smith']))->save();
        $credential = $this->createTestCredential();
        $timestamp = 1705315800; // 2024-01-15 10:30:00 UTC

        $passkey = $this->newPasskey()
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential)
            ->setLastLogin($timestamp);

        $this->assertInstanceOf(Carbon::class, $passkey->lastLogin());
        $this->assertEquals($timestamp, $passkey->lastLogin()->timestamp);
    }
}
