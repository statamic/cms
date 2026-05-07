<?php

namespace Tests\Auth\WebAuthn;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Eloquent\Passkey;
use Statamic\Auth\Eloquent\WebAuthnModel;
use Statamic\Facades\User;
use Symfony\Component\Uid\Uuid;
use Tests\TestCase;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

#[Group('passkeys')]
class EloquentPasskeyTest extends TestCase
{
    use PasskeyTests, RefreshDatabase;

    public static $migrationsGenerated = false;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config(['statamic.users.repository' => 'eloquent']);
        config(['statamic.webauthn.model' => WebAuthnModel::class]);

        $this->loadMigrationsFrom(static::migrationsDir());

        $tmpDir = static::migrationsDir().'/tmp';

        if (! self::$migrationsGenerated) {
            $this->artisan('statamic:auth:migration', ['--path' => $tmpDir]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);
    }

    private static function migrationsDir()
    {
        return __DIR__.'/../../Auth/Eloquent/__migrations__';
    }

    public function tearDown(): void
    {
        // Prevent error about null password during the down migration.
        User::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    protected function newPasskey(): \Statamic\Contracts\Auth\Passkey
    {
        return new Passkey;
    }

    protected function createTestCredential(string $id = 'test-credential-id-123'): PublicKeyCredentialSource
    {
        return PublicKeyCredentialSource::create(
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
    public function it_saves_passkey_to_database()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);

        $result = $passkey->save();

        $this->assertTrue($result);
        $this->assertDatabaseHas('webauthn', [
            'id' => Base64UrlSafe::encodeUnpadded('test-credential-id-123'),
            'user_id' => $user->id(),
            'name' => 'My Passkey',
        ]);
    }

    #[Test]
    public function it_saves_credential_as_json()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);
        $passkey->save();

        $model = WebAuthnModel::first();
        $this->assertIsArray($model->credential);
        $this->assertArrayHasKey('publicKeyCredentialId', $model->credential);
        $this->assertArrayHasKey('type', $model->credential);
    }

    #[Test]
    public function it_updates_existing_passkey()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
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
        $this->assertEquals(1, WebAuthnModel::count());
        $this->assertDatabaseHas('webauthn', [
            'id' => Base64UrlSafe::encodeUnpadded('test-credential-id-123'),
            'name' => 'Updated Passkey Name',
        ]);
    }

    #[Test]
    public function it_saves_multiple_passkeys_for_same_user()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
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

        $this->assertEquals(2, WebAuthnModel::where('user_id', $user->id())->count());
        $this->assertDatabaseHas('webauthn', ['name' => 'Passkey 1']);
        $this->assertDatabaseHas('webauthn', ['name' => 'Passkey 2']);
    }

    #[Test]
    public function it_deletes_passkey_from_database()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);
        $passkey->save();

        // Verify passkey exists
        $this->assertEquals(1, WebAuthnModel::count());

        // Delete the passkey
        $result = $passkey->delete();

        $this->assertTrue($result);
        $this->assertEquals(0, WebAuthnModel::count());
    }

    #[Test]
    public function it_deletes_only_specified_passkey()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
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

        $this->assertEquals(1, WebAuthnModel::count());
        $this->assertDatabaseMissing('webauthn', ['name' => 'Passkey 1']);
        $this->assertDatabaseHas('webauthn', ['name' => 'Passkey 2']);
    }

    #[Test]
    public function it_loads_passkey_from_database()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
        $user->save();

        $credential = $this->createTestCredential();
        $lastLogin = Carbon::create(2024, 1, 15, 10, 30, 0);

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential)
            ->setLastLogin($lastLogin);
        $passkey->save();

        // Load passkey from database via user
        $loadedPasskey = $user->passkeys()->first();

        $this->assertInstanceOf(Passkey::class, $loadedPasskey);
        $this->assertEquals('My Passkey', $loadedPasskey->name());
        $this->assertEquals('test-credential-id-123', $loadedPasskey->credential()->publicKeyCredentialId);
        $this->assertEquals('2024-01-15 10:30:00', $loadedPasskey->lastLogin()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_sets_timestamps_on_save()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
        $user->save();

        $credential = $this->createTestCredential();

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);
        $passkey->save();

        $model = WebAuthnModel::first();
        $this->assertNotNull($model->created_at);
        $this->assertNotNull($model->updated_at);
    }

    #[Test]
    public function it_uses_credential_id_as_primary_key()
    {
        $user = User::make()->email('test@example.com')->set('name', 'Test User');
        $user->save();

        $credential = $this->createTestCredential('my-unique-credential-id');

        $passkey = (new Passkey)
            ->setName('My Passkey')
            ->setUser($user)
            ->setCredential($credential);
        $passkey->save();

        $expectedId = Base64UrlSafe::encodeUnpadded('my-unique-credential-id');

        $model = WebAuthnModel::find($expectedId);
        $this->assertNotNull($model);
        $this->assertEquals($expectedId, $model->id);
    }
}
