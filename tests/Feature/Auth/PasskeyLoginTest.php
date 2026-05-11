<?php

namespace Tests\Feature\Auth;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('passkeys')]
class PasskeyLoginTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_request_options()
    {
        $response = $this->get(cp_route('passkeys.auth.options'));

        $response->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('challenge', $data);
        $this->assertArrayHasKey('userVerification', $data);
        $this->assertEquals('required', $data['userVerification']);
        $this->assertNotNull(session('webauthn.challenge'));
    }

    #[Test]
    public function it_generates_unique_challenge_on_each_request()
    {
        $response1 = $this->get(cp_route('passkeys.auth.options'));
        $challenge1 = $response1->json('challenge');

        $response2 = $this->get(cp_route('passkeys.auth.options'));
        $challenge2 = $response2->json('challenge');

        $this->assertNotEquals($challenge1, $challenge2);
    }

    #[Test]
    public function it_requires_user_verification()
    {
        $this
            ->get(cp_route('passkeys.auth.options'))
            ->assertOk()
            ->assertJson(['userVerification' => 'required']);
    }

    #[Test]
    public function it_stores_challenge_in_session_for_later_verification()
    {
        // Clear any existing session data
        session()->forget('webauthn.challenge');
        $this->assertNull(session('webauthn.challenge'));

        // Get options
        $response = $this->get(cp_route('passkeys.auth.options'));
        $responseChallenge = $response->json('challenge');

        // Verify the session has been populated
        $this->assertNotNull(session('webauthn.challenge'));

        // The session challenge is base64 encoded (32 bytes -> 44 chars)
        // The response challenge is the base64url encoded form
        $this->assertEquals(44, strlen(session('webauthn.challenge')));
        $this->assertEquals(32, strlen(base64_decode(session('webauthn.challenge'))));
        $this->assertIsString($responseChallenge);
    }

    private function loginRequest(?array $data = []): TestResponse
    {
        return $this->postJson(cp_route('passkeys.auth'), $data);
    }

    #[Test]
    public function it_successfully_logs_in_with_valid_passkey()
    {
        $user = $this->createUser();
        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andReturnTrue();

        $this
            ->loginRequest()
            ->assertOk()
            ->assertJson(['redirect' => cp_route('index')]);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_redirects_to_intended_url_after_successful_login()
    {
        $user = $this->createUser();
        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andReturnTrue();

        $this
            ->withSession(['url.intended' => 'http://localhost/cp/collections'])
            ->loginRequest()
            ->assertOk()
            ->assertJson(['redirect' => 'http://localhost/cp/collections']);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_fails_when_validation_throws_exception()
    {
        $user = $this->createUser();

        WebAuthn::shouldReceive('getUserFromCredentials')->once()->andReturn($user);
        WebAuthn::shouldReceive('validateAssertion')->once()->andThrow(new \Exception('No matching passkey found'));

        $this->loginRequest()->assertStatus(500);
        $this->assertGuest();
    }

    private function createUser()
    {
        return tap(User::make()->id('test-user')->email('test@example.com')->password('secret'))->save();
    }
}
