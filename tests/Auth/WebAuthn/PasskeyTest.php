<?php

namespace Tests\Auth\WebAuthn;

use Carbon\Carbon;
use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Passkey;
use Statamic\Facades\User;
use Symfony\Component\Uid\Uuid;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

#[Group('passkeys')]
class PasskeyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createTestCredential(): PublicKeyCredentialSource
    {
        return PublicKeyCredentialSource::create(
            publicKeyCredentialId: 'test-credential-id-123',
            type: 'public-key',
            transports: ['usb', 'nfc'],
            attestationType: 'none',
            trustPath: new EmptyTrustPath(),
            aaguid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            credentialPublicKey: 'test-public-key-data',
            userHandle: 'test-user-id',
            counter: 0
        );
    }

    #[Test]
    public function it_gets_credential()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $this->assertInstanceOf(PublicKeyCredentialSource::class, $passkey->credential());
        $this->assertEquals('test-credential-id-123', $passkey->credential()->publicKeyCredentialId);
        $this->assertEquals('public-key', $passkey->credential()->type);
    }

    #[Test]
    public function it_gets_id_from_credential()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $this->assertEquals(Base64UrlSafe::encodeUnpadded('test-credential-id-123'), $passkey->id());
    }

    #[Test]
    public function it_gets_name()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Test Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $this->assertEquals('My Test Passkey', $passkey->name());
    }

    #[Test]
    public function it_gets_user()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $this->assertInstanceOf(\Statamic\Contracts\Auth\User::class, $passkey->user());
        $this->assertEquals('test-user', $passkey->user()->id());
        $this->assertEquals('test@example.com', $passkey->user()->email());
    }

    #[Test]
    public function it_serializes()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $credential = $this->createTestCredential();
        $lastLogin = Carbon::create(2024, 1, 15, 10, 30, 0);

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential)
            ->setLastLogin($lastLogin);

        $serialized = serialize($passkey);
        $this->assertIsString($serialized);
        $this->assertStringContainsString('My Passkey', $serialized);
    }

    #[Test]
    public function it_unserializes()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential = $this->createTestCredential();
        $lastLogin = Carbon::create(2024, 1, 15, 10, 30, 0);

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential)
            ->setLastLogin($lastLogin);

        $serialized = serialize($passkey);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(Passkey::class, $unserialized);
        $this->assertEquals('My Passkey', $unserialized->name());
        $this->assertEquals('test-user', $unserialized->user()->id());
        $this->assertInstanceOf(PublicKeyCredentialSource::class, $unserialized->credential());
        $this->assertEquals('test-credential-id-123', $unserialized->credential()->publicKeyCredentialId);
        $this->assertEquals('2024-01-15 10:30:00', $unserialized->lastLogin()->format('Y-m-d H:i:s'));
    }
}
