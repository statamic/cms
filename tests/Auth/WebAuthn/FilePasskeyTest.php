<?php

namespace Tests\Auth\WebAuthn;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Passkey;
use Statamic\Facades\User;
use Symfony\Component\Uid\Uuid;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Webauthn\CredentialRecord;
use Webauthn\TrustPath\EmptyTrustPath;

#[Group('passkeys')]
class FilePasskeyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createTestCredential(string $id = 'test-credential-id-123'): CredentialRecord
    {
        return CredentialRecord::create(
            publicKeyCredentialId: $id,
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
    public function it_saves_passkey_to_user()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $result = $passkey->save();

        $this->assertTrue($result);

        // Verify the passkey was added to the user
        $freshUser = User::find('test-user');
        $this->assertCount(1, $freshUser->passkeys());
        $this->assertEquals('My Passkey', $freshUser->passkeys()->first()->name());
    }

    #[Test]
    public function it_updates_existing_passkey()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential = $this->createTestCredential();

        // Create and save initial passkey
        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);
        $passkey->save();

        // Update the passkey
        $passkey->setName('Updated Passkey Name');
        $passkey->setLastLogin(Carbon::create(2024, 1, 15, 10, 30, 0));
        $result = $passkey->save();

        $this->assertTrue($result);

        // Verify the passkey was updated (not duplicated)
        $freshUser = User::find('test-user');
        $this->assertCount(1, $freshUser->passkeys());
        $this->assertEquals('Updated Passkey Name', $freshUser->passkeys()->first()->name());
        $this->assertNotNull($freshUser->passkeys()->first()->lastLogin());
    }

    #[Test]
    public function it_saves_multiple_passkeys_to_same_user()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential1 = $this->createTestCredential('credential-1');
        $credential2 = $this->createTestCredential('credential-2');

        $passkey1 = (new Passkey)
            ->setName('Passkey 1')
            ->setUser($user)
            ->setCredential($credential1);
        $passkey1->save();

        $passkey2 = (new Passkey)
            ->setName('Passkey 2')
            ->setUser($user)
            ->setCredential($credential2);
        $passkey2->save();

        $freshUser = User::find('test-user');
        $this->assertCount(2, $freshUser->passkeys());

        $names = $freshUser->passkeys()->map->name()->values();
        $this->assertTrue($names->contains('Passkey 1'));
        $this->assertTrue($names->contains('Passkey 2'));
    }

    #[Test]
    public function it_deletes_passkey_from_user()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);
        $passkey->save();

        // Verify passkey exists
        $this->assertCount(1, User::find('test-user')->passkeys());

        // Delete the passkey
        $result = $passkey->delete();

        $this->assertTrue($result);

        // Verify passkey was removed
        $freshUser = User::find('test-user');
        $this->assertCount(0, $freshUser->passkeys());
    }

    #[Test]
    public function it_deletes_only_specified_passkey()
    {
        $user = User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credential1 = $this->createTestCredential('credential-1');
        $credential2 = $this->createTestCredential('credential-2');

        $passkey1 = (new Passkey)
            ->setName('Passkey 1')
            ->setUser($user)
            ->setCredential($credential1);
        $passkey1->save();

        $passkey2 = (new Passkey)
            ->setName('Passkey 2')
            ->setUser($user)
            ->setCredential($credential2);
        $passkey2->save();

        // Delete only the first passkey
        $passkey1->delete();

        $freshUser = User::find('test-user');
        $this->assertCount(1, $freshUser->passkeys());
        $this->assertEquals('Passkey 2', $freshUser->passkeys()->first()->name());
    }

    #[Test]
    public function it_persists_all_passkey_data()
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
        $passkey->save();

        $freshUser = User::find('test-user');
        $savedPasskey = $freshUser->passkeys()->first();

        $this->assertEquals('My Passkey', $savedPasskey->name());
        $this->assertEquals('test-credential-id-123', $savedPasskey->credential()->publicKeyCredentialId);
        $this->assertEquals('2024-01-15 10:30:00', $savedPasskey->lastLogin()->format('Y-m-d H:i:s'));
        $this->assertEquals('test-user', $savedPasskey->user()->id());
    }
}
