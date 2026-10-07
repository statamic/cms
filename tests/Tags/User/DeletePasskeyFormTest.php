<?php

namespace Tests\Tags\User;

use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('passkeys')]
class DeletePasskeyFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_returns_empty_when_not_logged_in()
    {
        $output = $this->tag('{{ user:delete_passkey_form id="passkey-123" }}Delete{{ /user:delete_passkey_form }}');

        $this->assertEquals('', $output);
    }

    #[Test]
    public function it_returns_empty_when_passkey_not_found()
    {
        $user = Mockery::mock(User::make()->email('test@example.com'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn(collect([]));
        $user->save();

        $this->actingAs($user);

        $output = $this->tag('{{ user:delete_passkey_form id="nonexistent" }}Delete{{ /user:delete_passkey_form }}');

        $this->assertEquals('', $output);
    }

    #[Test]
    public function it_renders_form()
    {
        $passkey = Mockery::mock(Passkey::class);
        $passkey->shouldReceive('id')->andReturn('passkey-123');

        $user = Mockery::mock(User::make()->email('test@example.com'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn(collect(['passkey-123' => $passkey]));
        $user->save();

        $this->actingAs($user);

        $output = $this->tag('{{ user:delete_passkey_form id="passkey-123" }}<button>Delete</button>{{ /user:delete_passkey_form }}');

        $this->assertStringContainsString('<form', $output);
        $this->assertStringContainsString('method="POST"', $output);
        $this->assertStringContainsString('action="'.route('statamic.passkeys.destroy', ['id' => 'passkey-123']).'"', $output);
        $this->assertStringContainsString('name="_method"', $output);
        $this->assertStringContainsString('value="DELETE"', $output);
        $this->assertStringContainsString('name="_token"', $output);
        $this->assertStringContainsString('<button>Delete</button>', $output);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $passkey = Mockery::mock(Passkey::class);
        $passkey->shouldReceive('id')->andReturn('passkey-789');

        $user = Mockery::mock(User::make()->email('test@example.com'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn(collect(['passkey-789' => $passkey]));
        $user->save();

        $this->actingAs($user);

        $form = Statamic::tag('user:delete_passkey_form')->params(['id' => 'passkey-789'])->fetch();

        $this->assertIsArray($form);
        $this->assertArrayHasKey('attrs', $form);
        $this->assertArrayHasKey('params', $form);
        $this->assertEquals('DELETE', $form['params']['_method']);
    }

    #[Test]
    public function it_requires_authentication_for_delete()
    {
        $this->deleteJson(route('statamic.passkeys.destroy', ['id' => 'passkey-123']))->assertUnauthorized();
    }

    #[Test]
    public function it_deletes_a_passkey_via_json()
    {
        $mockPasskey = Mockery::mock(Passkey::class);
        $mockPasskey->shouldReceive('delete')->once();

        $mockCollection = collect(['passkey-123' => $mockPasskey]);
        $mockCollection = Mockery::mock($mockCollection)->makePartial();
        $mockCollection->shouldReceive('get')->with('passkey-123')->andReturn($mockPasskey);

        $user = Mockery::mock(User::make()->id('test-user')->email('test@example.com')->password('secret'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn($mockCollection);
        $user->save();

        $this
            ->actingAs($user)
            ->deleteJson(route('statamic.passkeys.destroy', ['id' => 'passkey-123']))
            ->assertStatus(204);
    }

    #[Test]
    public function it_deletes_a_passkey_via_form_and_redirects()
    {
        $mockPasskey = Mockery::mock(Passkey::class);
        $mockPasskey->shouldReceive('delete')->once();

        $mockCollection = collect(['passkey-123' => $mockPasskey]);
        $mockCollection = Mockery::mock($mockCollection)->makePartial();
        $mockCollection->shouldReceive('get')->with('passkey-123')->andReturn($mockPasskey);

        $user = Mockery::mock(User::make()->id('test-user')->email('test@example.com')->password('secret'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn($mockCollection);
        $user->save();

        $this
            ->actingAs($user)
            ->from('/account/passkeys')
            ->delete(route('statamic.passkeys.destroy', ['id' => 'passkey-123']))
            ->assertRedirect('/account/passkeys')
            ->assertSessionHas('success');
    }

    #[Test]
    public function it_returns_403_when_deleting_nonexistent_passkey()
    {
        $mockCollection = collect([]);
        $mockCollection = Mockery::mock($mockCollection)->makePartial();
        $mockCollection->shouldReceive('get')->with('nonexistent')->andReturnNull();

        $user = Mockery::mock(User::make()->id('test-user')->email('test@example.com')->password('secret'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn($mockCollection);
        $user->save();

        $this
            ->actingAs($user)
            ->deleteJson(route('statamic.passkeys.destroy', ['id' => 'nonexistent']))
            ->assertStatus(403);
    }
}
