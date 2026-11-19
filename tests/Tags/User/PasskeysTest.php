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
class PasskeysTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_returns_no_results_when_not_logged_in()
    {
        $output = $this->tag('{{ user:passkeys }}{{ name }}{{ /user:passkeys }}');

        $this->assertEquals('', $output);
    }

    #[Test]
    public function it_returns_no_results_when_user_has_no_passkeys()
    {
        $user = User::make()->email('test@example.com');
        $user->save();

        $this->actingAs($user);

        $output = $this->tag('{{ user:passkeys }}{{ name }}{{ /user:passkeys }}{{ unless user:passkeys }}no-passkeys{{ /unless }}');

        $this->assertStringContainsString('no-passkeys', (string) $output);
    }

    #[Test]
    public function it_lists_user_passkeys()
    {
        $passkey1 = Mockery::mock(Passkey::class);
        $passkey1->shouldReceive('id')->andReturn('passkey-1');
        $passkey1->shouldReceive('name')->andReturn('My Laptop');
        $passkey1->shouldReceive('lastLogin')->andReturn(now()->subDay());

        $passkey2 = Mockery::mock(Passkey::class);
        $passkey2->shouldReceive('id')->andReturn('passkey-2');
        $passkey2->shouldReceive('name')->andReturn('My Phone');
        $passkey2->shouldReceive('lastLogin')->andReturn(null);

        $user = Mockery::mock(User::make()->email('test@example.com'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn(collect([
            'passkey-1' => $passkey1,
            'passkey-2' => $passkey2,
        ]));
        $user->save();

        $this->actingAs($user);

        $output = $this->tag('{{ user:passkeys }}{{ name }},{{ /user:passkeys }}');

        $this->assertStringContainsString('My Laptop', $output);
        $this->assertStringContainsString('My Phone', $output);
    }

    #[Test]
    public function it_fetches_data_without_content()
    {
        $passkey = Mockery::mock(Passkey::class);
        $passkey->shouldReceive('id')->andReturn('passkey-123');
        $passkey->shouldReceive('name')->andReturn('Test');
        $passkey->shouldReceive('lastLogin')->andReturn(null);

        $user = Mockery::mock(User::make()->email('test@example.com'))->makePartial();
        $user->shouldReceive('passkeys')->andReturn(collect(['passkey-123' => $passkey]));
        $user->save();

        $this->actingAs($user);

        $passkeys = Statamic::tag('user:passkeys')->fetch();

        $this->assertIsArray($passkeys);
        $this->assertCount(1, $passkeys);
        $this->assertEquals('passkey-123', $passkeys[0]['id']);
        $this->assertEquals('Test', $passkeys[0]['name']);
    }

    #[Test]
    public function it_returns_empty_array_when_not_logged_in_without_content()
    {
        $passkeys = Statamic::tag('user:passkeys')->fetch();

        $this->assertIsArray($passkeys);
        $this->assertEmpty($passkeys);
    }
}
