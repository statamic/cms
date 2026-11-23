<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use ParagonIE\ConstantTime\Base64UrlSafe;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Passkey;
use Statamic\Auth\File\User;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Events\UserCreated;
use Statamic\Events\UserSaved;
use Statamic\Facades\File;
use Statamic\Facades\Role;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Facades\Stache;
use Statamic\Facades\User as UserAPI;
use Statamic\Facades\UserGroup;
use Statamic\Facades\UserGroup as UserGroupAPI;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Uid\Uuid;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

#[Group('user')]
#[Group('2fa')]
class FileUserTest extends TestCase
{
    use PermissibleContractTests, PreventSavingStacheItemsToDisk, UnsafeEmailPayloads, UserContractTests;

    public function makeUser()
    {
        return new User;
    }

    public function createPermissible()
    {
        return $this->makeUser();
    }

    #[Test]
    public function it_gets_path()
    {
        $this->assertEquals($this->fakeStacheDirectory.'/users/john@example.com.yaml', $this->user()->path());
    }

    #[Test]
    #[DataProvider('unsafeEmailProvider')]
    public function it_cannot_get_a_path_for_an_unsafe_email($email)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeUser()->email($email)->path();
    }

    #[Test]
    #[DataProvider('dotEmailProvider')]
    public function it_cannot_get_a_path_for_a_dot_email($email)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeUser()->email($email)->path();
    }

    public static function dotEmailProvider(): array
    {
        return [
            'dot' => ['.'],
            'dot dot' => ['..'],
        ];
    }

    #[Test]
    public function hashed_password_gets_added_as_the_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->data(['password_hash' => bcrypt('secret')]);

        $this->assertNotNull($user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertFalse($user->has('password_hash'));
    }

    #[Test]
    public function it_gets_file_contents_for_saving()
    {
        Hash::shouldReceive('make')->with('secret')->andReturn('hashed-secret');

        $user = $this->user()->password('secret');

        $this->assertEquals([
            'name' => 'John Smith',
            'foo' => 'bar',
            'roles' => [
                'role_one',
                'role_two',
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
            'id' => '123',
            'password_hash' => 'hashed-secret',
            'content' => 'Lorem Ipsum',
            'preferences' => [
                'locale' => 'en',
            ],
        ], Arr::removeNullValues($user->fileData()));
    }

    #[Test]
    public function it_gets_permissions_from_a_cache()
    {
        $directRole = $this->mock(RoleContract::class);
        $userGroupRole = $this->mock(RoleContract::class);
        $userGroup = $this->mock(UserGroupContract::class);

        $directRole->shouldReceive('id')->andReturn('direct');
        $directRole->shouldReceive('handle')->andReturn('direct');
        $directRole->shouldReceive('permissions')->once()->andReturn(collect([
            'permission one directly through role',
            'permission two directly through role',
        ]));

        $userGroupRole->shouldReceive('id')->andReturn('usergrouprole');
        $userGroupRole->shouldReceive('handle')->andReturn('usergrouprole');
        $userGroupRole->shouldReceive('permissions')->twice()->andReturn(collect([
            'permission one through user group',
            'permission two through user group',
        ]));

        $userGroup->shouldReceive('id')->andReturn('usergroup');
        $userGroup->shouldReceive('handle')->andReturn('usergroup');
        $userGroup->shouldReceive('roles')->once()->andReturn(collect([$userGroupRole]))->times(4);

        Role::shouldReceive('find')->with('direct')->andReturn($directRole);
        Role::shouldReceive('all')->andReturn(collect([$directRole])); // the stache calls this when getting a user. unrelated to test.
        UserGroup::shouldReceive('find')->with('usergroup')->andReturn($userGroup);
        Role::shouldReceive('all')->andReturn(collect([$directRole]));     // the stache calls this when getting a user. unrelated to test.
        UserGroup::shouldReceive('all')->andReturn(collect([$userGroup])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible()
            ->assignRole($directRole)
            ->addToGroup($userGroup);
        $user->save();

        $expectedPermissions = [
            'permission one through user group',
            'permission two through user group',
            'permission one directly through role',
            'permission two directly through role',
        ];
        $this->assertEquals($expectedPermissions, $user->permissions()->all());

        // Doing it a second time should give the same result but without multiple calls.
        $this->assertEquals($expectedPermissions, $user->permissions()->all());
    }

    #[Test]
    public function it_prevents_saving_duplicate_roles()
    {
        $roleA = (new \Statamic\Auth\File\Role)->handle('a');
        $roleB = (new \Statamic\Auth\File\Role)->handle('b');
        $roleC = (new \Statamic\Auth\File\Role)->handle('c');

        RoleAPI::shouldReceive('find')->with('a')->andReturn($roleA);
        RoleAPI::shouldReceive('find')->with('b')->andReturn($roleB);
        RoleAPI::shouldReceive('find')->with('c')->andReturn($roleC);
        RoleAPI::shouldReceive('all')->andReturn(collect([$roleA, $roleB])); // the stache calls this when getting a user. unrelated to test.

        $user = $this->createPermissible();
        $user->assignRole('a');

        $this->assertEquals(['a'], $user->get('roles'));

        $user->assignRole(['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $user->get('roles'));
    }

    #[Test]
    public function it_prevents_saving_duplicate_groups()
    {
        $groupA = (new \Statamic\Auth\File\UserGroup)->handle('a');
        $groupB = (new \Statamic\Auth\File\UserGroup)->handle('b');
        $groupC = (new \Statamic\Auth\File\UserGroup)->handle('c');

        UserGroupAPI::shouldReceive('find')->with('a')->andReturn($groupA);
        UserGroupAPI::shouldReceive('find')->with('b')->andReturn($groupB);
        UserGroupAPI::shouldReceive('find')->with('c')->andReturn($groupC);

        $user = $this->createPermissible();
        $user->addToGroup('a');

        $this->assertEquals(['a'], $user->get('groups'));

        $user->addToGroup(['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $user->get('groups'));
    }

    #[Test]
    public function it_clones_internal_collections()
    {
        $user = $this->user();
        $user->set('foo', 'A');
        $user->setSupplement('bar', 'A');

        $clone = clone $user;
        $clone->set('foo', 'B');
        $clone->setSupplement('bar', 'B');

        $this->assertEquals('A', $user->get('foo'));
        $this->assertEquals('B', $clone->get('foo'));

        $this->assertEquals('A', $user->getSupplement('bar'));
        $this->assertEquals('B', $clone->getSupplement('bar'));
    }

    #[Test]
    public function it_does_not_save_a_user_that_was_deleted_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->password('secret');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->delete();

        Event::fake();

        $this->assertFalse($copy->password('new-secret')->save());
        $this->assertFileDoesNotExist($path);
        $this->assertNull(UserAPI::find('123'));
        Event::assertNotDispatched(UserCreated::class);
        Event::assertNotDispatched(UserSaved::class);
    }

    #[Test]
    public function it_saves_a_user_again_after_deleting_it()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com');
        $user->save();
        $user->delete();

        $this->assertFileDoesNotExist($user->path());

        $this->assertSame($user, $user->save());
        $this->assertFileExists($user->path());
        $this->assertNotNull(UserAPI::find('123'));
    }

    #[Test]
    public function it_keeps_access_changed_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->assignRole('author')->addToGroup('editors')->makeSuper();
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->removeRole('author')->removeFromGroup('editors')->set('super', false)->save();

        $copy->password('new-secret')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertEmpty($yaml['roles'] ?? []);
        $this->assertEmpty($yaml['groups'] ?? []);
        $this->assertFalse($yaml['super'] ?? false);
        $this->assertEmpty(UserAPI::find('123')->get('roles'));
        $this->assertTrue(Hash::check('new-secret', UserAPI::find('123')->password()));
    }

    #[Test]
    public function it_saves_access_changed_through_a_copy_loaded_before_another_change()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->assignRole('author');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->addToGroup('editors')->save();

        $copy->assignRole('publisher')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertEquals(['author', 'publisher'], $yaml['roles'] ?? []);
        $this->assertEquals(['editors'], $yaml['groups'] ?? []);
    }

    #[Test]
    public function it_keeps_a_password_reset_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->password('old-secret');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->password('reset-secret')->save();

        $copy->set('name', 'John')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertTrue(Hash::check('reset-secret', $yaml['password_hash']));
        $this->assertSame('John', $yaml['name']);
        $this->assertTrue(Hash::check('reset-secret', UserAPI::find('123')->password()));
    }

    #[Test]
    public function it_hashes_a_plain_text_password_from_its_file()
    {
        $path = $this->makeUser()->email('john@example.com')->path();
        File::put($path, "id: '123'\npassword: secret\n");

        $user = Stache::store('users')->makeItemFromFile($path, File::get($path));

        $yaml = YAML::file($path)->parse();
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertTrue(Hash::check('secret', $yaml['password_hash']));
        $this->assertArrayNotHasKey('password', $yaml);
    }

    #[Test]
    public function it_keeps_its_own_access_when_its_file_is_read_while_empty()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->password('secret')->assignRole('author')->makeSuper();
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        File::put($path, '');

        $copy->set('name', 'John')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertTrue(Hash::check('secret', $yaml['password_hash']));
        $this->assertSame(['author'], $yaml['roles']);
        $this->assertTrue($yaml['super']);
        $this->assertSame('John', $yaml['name']);
    }

    #[Test]
    public function it_keeps_its_own_password_when_its_file_is_read_while_partially_written()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->password('secret')->assignRole('author');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        File::put($path, Str::before(File::get($path), 'password_hash'));

        $copy->set('name', 'John')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertTrue(Hash::check('secret', $yaml['password_hash']));
        $this->assertSame(['author'], $yaml['roles']);
        $this->assertSame('John', $yaml['name']);
    }

    #[Test]
    public function it_deletes_a_user_whose_email_was_changed_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->assignRole('author');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->email('jane@example.com')->save();

        $copy->delete();

        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist(dirname($path).'/jane@example.com.yaml');

        Stache::clear();

        $this->assertNull(UserAPI::find('123'));
    }

    #[Test]
    public function it_keeps_an_email_changed_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')->assignRole('author');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->email('jane@example.com')->save();

        $copy->removeRole('author')->save();

        $newPath = dirname($path).'/jane@example.com.yaml';
        $this->assertFileDoesNotExist($path);
        $this->assertFileExists($newPath);
        $this->assertEmpty(YAML::file($newPath)->parse()['roles'] ?? []);
        $this->assertSame('jane@example.com', $copy->email());

        Stache::clear();

        $this->assertEmpty(UserAPI::find('123')->get('roles'));
    }

    #[Test]
    public function it_moves_its_file_when_its_own_email_is_changed_after_the_file_was_renamed_elsewhere()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->email('jane@example.com')->save();

        $copy->email('jack@example.com')->save();

        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist(dirname($path).'/jane@example.com.yaml');
        $this->assertFileExists(dirname($path).'/jack@example.com.yaml');
    }

    #[Test]
    public function it_does_not_delete_another_users_file_at_its_old_path()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com');
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->email('jane@example.com')->save();
        $this->makeUser()->id('456')->email('john@example.com')->save();

        $copy->delete();

        $this->assertSame('456', (string) YAML::file($path)->parse()['id']);
        $this->assertFileDoesNotExist(dirname($path).'/jane@example.com.yaml');
    }

    #[Test]
    public function it_keeps_two_factor_changes_made_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com')
            ->set('two_factor_secret', 'old-secret')
            ->set('two_factor_recovery_codes', 'old-codes')
            ->set('two_factor_confirmed_at', 1000);
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->set('two_factor_recovery_codes', 'new-codes')->save();

        $copy->set('name', 'John')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertSame('new-codes', $yaml['two_factor_recovery_codes']);
        $this->assertSame('old-secret', $yaml['two_factor_secret']);
        $this->assertSame('John', $yaml['name']);

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->remove('two_factor_secret')->remove('two_factor_recovery_codes')->remove('two_factor_confirmed_at')->save();

        $copy->set('name', 'Johnny')->save();

        $yaml = YAML::file($path)->parse();
        $this->assertArrayNotHasKey('two_factor_secret', $yaml);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $yaml);
        $this->assertArrayNotHasKey('two_factor_confirmed_at', $yaml);
        $this->assertSame('Johnny', $yaml['name']);
    }

    #[Test]
    #[Group('passkeys')]
    public function it_keeps_passkeys_removed_after_it_was_loaded()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com');
        $user->setPasskeys(collect([
            $this->passkey($user, 'A'),
            $this->passkey($user, 'B'),
        ]));
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        $other = UserAPI::find('123');
        $other->passkeys()->first()->delete();

        $copy->set('name', 'John')->save();

        $this->assertSame(['B'], array_column(YAML::file($path)->parse()['passkeys'], 'name'));

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->passkeys()->first()->delete();

        $copy->set('name', 'Johnny')->save();

        $this->assertArrayNotHasKey('passkeys', YAML::file($path)->parse());
    }

    #[Test]
    #[Group('passkeys')]
    public function it_saves_passkeys_changed_through_a_copy_loaded_before_another_change()
    {
        $user = $this->makeUser()->id('123')->email('john@example.com');
        $user->setPasskeys(collect([
            $this->passkey($user, 'A'),
        ]));
        $user->save();
        $path = $user->path();

        $copy = Stache::store('users')->makeItemFromFile($path, File::get($path));
        UserAPI::find('123')->set('name', 'John')->save();

        $copy->setPasskeys($copy->passkeys()->push($this->passkey($copy, 'B')));
        $copy->save();

        $yaml = YAML::file($path)->parse();
        $this->assertSame(['A', 'B'], array_column($yaml['passkeys'], 'name'));
    }

    private function passkey(User $user, string $name): Passkey
    {
        $credential = new PublicKeyCredentialSource('key-'.$name, 'public-key', [], 'none', new EmptyTrustPath, Uuid::v4(), 'public-key-'.$name, 'user-handle', 0);

        return (new Passkey)->setUser($user)->setName($name)->setCredential($credential);
    }

    #[Test]
    #[Group('passkeys')]
    public function it_gets_passkeys()
    {
        $user = $this->user();
        $this->assertCount(0, $user->passkeys());

        $mockCredentialA = \Mockery::mock(PublicKeyCredentialSource::class);
        $mockCredentialA->publicKeyCredentialId = 'key-a';
        $mockCredentialB = \Mockery::mock(PublicKeyCredentialSource::class);
        $mockCredentialB->publicKeyCredentialId = 'key-b';

        $user->setPasskeys(collect([
            $passkeyA = (new Passkey)->setCredential($mockCredentialA),
            $passkeyB = (new Passkey)->setCredential($mockCredentialB),
        ]));

        $this->assertCount(2, $passkeys = $user->passkeys());
        $this->assertEquals([
            Base64UrlSafe::encodeUnpadded('key-a') => $passkeyA,
            Base64UrlSafe::encodeUnpadded('key-b') => $passkeyB,
        ], $passkeys->all());
    }
}
