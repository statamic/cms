<?php

namespace Tests\Tags\User;

use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('passkeys')]
class PasskeyFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_urls()
    {
        $output = $this->tag('{{ user:passkey_form }}{{ passkey_options_url }}|{{ passkey_verify_url }}{{ /user:passkey_form }}');

        $this->assertStringContainsString(route('statamic.passkeys.create'), $output);
        $this->assertStringContainsString(route('statamic.passkeys.store'), $output);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $form = Statamic::tag('user:passkey_form')->fetch();

        $this->assertIsArray($form);
        $this->assertEquals(route('statamic.passkeys.create'), $form['passkey_options_url']);
        $this->assertEquals(route('statamic.passkeys.store'), $form['passkey_verify_url']);
    }

    #[Test]
    public function it_requires_authentication_for_create_options()
    {
        $this->getJson(route('statamic.passkeys.create'))->assertUnauthorized();
    }

    #[Test]
    public function it_requires_authentication_for_store()
    {
        $this->postJson(route('statamic.passkeys.store'))->assertUnauthorized();
    }

    #[Test]
    public function it_gets_creation_options()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        $response = $this
            ->actingAs($user)
            ->get(route('statamic.passkeys.create'))
            ->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('challenge', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('rp', $data);
    }

    #[Test]
    public function it_stores_a_passkey()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        $mockPasskey = Mockery::mock(Passkey::class);

        $payload = [
            'id' => 'credential-id',
            'rawId' => 'raw-id',
            'response' => [],
            'type' => 'public-key',
        ];

        WebAuthn::shouldReceive('validateAttestation')
            ->once()
            ->with($user, $payload, 'Test Passkey')
            ->andReturn($mockPasskey);

        $this
            ->actingAs($user)
            ->postJson(route('statamic.passkeys.store'), [
                ...$payload,
                'name' => 'Test Passkey',
            ])
            ->assertOk()
            ->assertJson(['verified' => true]);
    }

    #[Test]
    public function it_stores_a_passkey_with_default_name()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        $mockPasskey = Mockery::mock(Passkey::class);

        $payload = [
            'id' => 'credential-id',
            'rawId' => 'raw-id',
            'response' => [],
            'type' => 'public-key',
        ];

        WebAuthn::shouldReceive('validateAttestation')
            ->once()
            ->with($user, $payload, 'Passkey')
            ->andReturn($mockPasskey);

        $this
            ->actingAs($user)
            ->postJson(route('statamic.passkeys.store'), $payload)
            ->assertOk()
            ->assertJson(['verified' => true]);
    }

    #[Test]
    public function it_fails_storing_when_validation_throws_exception()
    {
        $user = User::make()->id('test-user')->email('test@example.com')->password('secret');
        $user->save();

        WebAuthn::shouldReceive('validateAttestation')
            ->once()
            ->andThrow(new \Exception('Invalid credentials'));

        $this
            ->actingAs($user)
            ->postJson(route('statamic.passkeys.store'), [
                'id' => 'credential-id',
                'rawId' => 'raw-id',
                'response' => [],
                'type' => 'public-key',
            ])
            ->assertStatus(500);
    }
}
