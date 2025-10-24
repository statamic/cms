<?php

namespace Tests\Feature\Auth;

use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('passkeys')]
class DeletePasskeyTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    private function deleteRequest($id)
    {
        return $this->deleteJson(cp_route('passkeys.destroy', ['id' => $id]));
    }

    #[Test]
    public function it_deletes_a_passkey()
    {
        $user = $this->createUser();

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockPasskey->shouldReceive('delete')->once();

        $mockCollection = collect(['passkey-123' => $mockPasskey]);
        $mockCollection = Mockery::mock($mockCollection)->makePartial();
        $mockCollection->shouldReceive('get')->with('passkey-123')->andReturn($mockPasskey);

        $user->shouldReceive('passkeys')->andReturn($mockCollection);

        $this
            ->actingAs($user)
            ->deleteRequest('passkey-123')
            ->assertStatus(201);
    }

    #[Test]
    public function it_returns_403_when_passkey_not_found()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $mockCollection = collect([]);
        $mockCollection = Mockery::mock($mockCollection)->makePartial();
        $mockCollection->shouldReceive('get')->with('nonexistent-passkey')->andReturnNull();

        $user->shouldReceive('passkeys')->andReturn($mockCollection);

        $this->deleteRequest('nonexistent-passkey')->assertStatus(403);
    }

    #[Test]
    public function it_prevents_deleting_other_users_passkeys()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $mockCollection = collect([]);
        $mockCollection = Mockery::mock($mockCollection)->makePartial();
        $mockCollection->shouldReceive('get')
            ->with('other-passkey')
            ->andReturn(null); // Passkey not found in current user's collection

        $user->shouldReceive('passkeys')->andReturn($mockCollection);

        $this->deleteRequest('other-passkey')->assertStatus(403);
    }

    private function createUser()
    {
        $this->setTestRole('test', ['access cp']);

        $user = Mockery::mock(User::make()->id('test-user')->email('test@example.com')->password('secret'))->makePartial();
        $user->assignRole('test');
        $user->save();

        return $user;
    }
}
