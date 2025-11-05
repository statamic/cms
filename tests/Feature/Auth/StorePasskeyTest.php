<?php

namespace Tests\Feature\Auth;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('passkeys')]
class StorePasskeyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createOptionsRequest(): TestResponse
    {
        return $this->get(cp_route('passkeys.create'));
    }

    private function storeRequest(?array $data = []): TestResponse
    {
        return $this->postJson(cp_route('passkeys.store'), $data);
    }

    #[Test]
    public function it_gets_creation_options()
    {
        $response = $this
            ->actingAs($this->createUser())
            ->createOptionsRequest()
            ->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('challenge', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('rp', $data);
        $this->assertNotNull(session('webauthn.challenge'));
    }

    #[Test]
    public function it_generates_unique_challenge_on_each_request()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response1 = $this->createOptionsRequest();
        $challenge1 = $response1->json('challenge');

        $response2 = $this->createOptionsRequest();
        $challenge2 = $response2->json('challenge');

        $this->assertNotEquals($challenge1, $challenge2);
    }

    #[Test]
    public function it_stores_challenge_in_session_during_creation()
    {
        $this->actingAs($this->createUser());

        session()->forget('webauthn.challenge');
        $this->assertNull(session('webauthn.challenge'));

        $response = $this->createOptionsRequest();
        $responseChallenge = $response->json('challenge');

        $this->assertNotNull(session('webauthn.challenge'));
        $this->assertEquals(16, strlen(session('webauthn.challenge')));
        $this->assertIsString($responseChallenge);
    }

    #[Test]
    public function it_stores_a_passkey()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $mockPasskey = \Mockery::mock(Passkey::class);

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
            ->storeRequest([
                ...$payload,
                'name' => 'Test Passkey',
            ])
            ->assertOk()
            ->assertJson(['verified' => true]);
    }

    #[Test]
    public function it_fails_when_validation_throws_exception()
    {
        WebAuthn::shouldReceive('validateAttestation')
            ->once()
            ->andThrow(new \Exception('Invalid credentials'));

        $this
            ->actingAs($this->createUser())
            ->storeRequest([
                'id' => 'credential-id',
                'rawId' => 'raw-id',
                'response' => [],
                'type' => 'public-key',
                'name' => 'My Passkey',
            ])
            ->assertStatus(500);
    }

    private function createUser()
    {
        $user = tap(User::make()->id('test-user')->email('test@example.com')->password('secret'))->save();
        $user->makeSuper();

        return $user;
    }
}
