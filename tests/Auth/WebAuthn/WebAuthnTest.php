<?php

namespace Tests\Auth\WebAuthn;

use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Auth\WebAuthn\WebAuthn;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User as UserFacade;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorData;
use Webauthn\CollectedClientData;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

#[Group('passkeys')]
class WebAuthnTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private AuthenticatorAssertionResponseValidator $mockAssertionValidator;
    private AuthenticatorAttestationResponseValidator $mockAttestationValidator;
    private Serializer $mockSerializer;
    private PublicKeyCredentialRpEntity $mockRpEntity;
    private WebAuthn $webauthn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAssertionValidator = Mockery::mock(AuthenticatorAssertionResponseValidator::class);
        $this->mockAttestationValidator = Mockery::mock(AuthenticatorAttestationResponseValidator::class);
        $this->mockSerializer = Mockery::mock(Serializer::class);
        $this->mockRpEntity = PublicKeyCredentialRpEntity::create('localhost');
        $this->webauthn = new WebAuthn(
            $this->mockAssertionValidator,
            $this->mockAttestationValidator,
            $this->mockSerializer,
            $this->mockRpEntity
        );
    }

    #[Test]
    public function it_prepares_assertion_with_challenge()
    {
        session()->forget('webauthn.challenge');

        $options = $this->webauthn->prepareAssertion();

        $this->assertInstanceOf(PublicKeyCredentialRequestOptions::class, $options);
        $this->assertNotNull(session('webauthn.challenge'));
        $this->assertEquals(32, strlen(session('webauthn.challenge')));
        $this->assertEquals(PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED, $options->userVerification);
    }

    #[Test]
    public function it_generates_unique_challenges_for_each_assertion()
    {
        $options1 = $this->webauthn->prepareAssertion();
        $challenge1 = session('webauthn.challenge');

        $options2 = $this->webauthn->prepareAssertion();
        $challenge2 = session('webauthn.challenge');

        $this->assertNotEquals($challenge1, $challenge2);
        $this->assertNotEquals($options1->challenge, $options2->challenge);
    }

    #[Test]
    public function it_stores_challenge_in_session()
    {
        session()->forget('webauthn.challenge');
        $this->assertNull(session('webauthn.challenge'));

        $options = $this->webauthn->prepareAssertion();

        // Challenge should be stored in session for later verification
        $storedChallenge = session('webauthn.challenge');
        $this->assertNotNull($storedChallenge);
        $this->assertEquals(32, strlen($storedChallenge));

        // The challenge in the options should be the same binary value
        // (base64url encoding happens during serialization, not in the object)
        $this->assertEquals($storedChallenge, $options->challenge);
    }

    #[Test]
    public function it_gets_user_from_credentials()
    {
        $user = \Statamic\Facades\User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credentials = ['id' => 'credential-id', 'rawId' => 'raw-id', 'response' => [], 'type' => 'public-key'];

        // Create a real PublicKeyCredential with mocked dependencies
        $publicKeyCredential = new PublicKeyCredential(
            'public-key',
            'test-raw-id',
            new AuthenticatorAssertionResponse(
                Mockery::mock(CollectedClientData::class),
                Mockery::mock(AuthenticatorData::class),
                'signature',
                'test-user' // userHandle
            )
        );

        $this->mockSerializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(json_encode($credentials), PublicKeyCredential::class, 'json')
            ->andReturn($publicKeyCredential);

        $result = $this->webauthn->getUserFromCredentials($credentials);

        $this->assertEquals($user->id(), $result->id());
    }

    #[Test]
    public function it_throws_exception_when_user_not_found()
    {
        $credentials = ['id' => 'credential-id', 'rawId' => 'raw-id', 'response' => [], 'type' => 'public-key'];

        $publicKeyCredential = new PublicKeyCredential(
            'public-key',
            'test-raw-id',
            new AuthenticatorAssertionResponse(
                Mockery::mock(CollectedClientData::class),
                Mockery::mock(AuthenticatorData::class),
                'signature',
                'nonexistent-user'
            )
        );

        $this->mockSerializer
            ->shouldReceive('deserialize')
            ->once()
            ->andReturn($publicKeyCredential);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found');

        $this->webauthn->getUserFromCredentials($credentials);
    }

    #[Test]
    public function it_throws_exception_when_response_is_not_authenticator_assertion()
    {
        $credentials = ['id' => 'credential-id', 'rawId' => 'raw-id', 'response' => [], 'type' => 'public-key'];

        // Create a credential with a different response type (not AuthenticatorAssertionResponse)
        $publicKeyCredential = new PublicKeyCredential(
            'public-key',
            'test-raw-id',
            Mockery::mock(\Webauthn\AuthenticatorResponse::class) // Different response type
        );

        $this->mockSerializer
            ->shouldReceive('deserialize')
            ->once()
            ->andReturn($publicKeyCredential);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->webauthn->getUserFromCredentials($credentials);
    }

    #[Test]
    public function it_validates_assertion_successfully()
    {
        $user = \Statamic\Facades\User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credentials = ['id' => 'credential-id', 'rawId' => 'raw-id', 'response' => [], 'type' => 'public-key'];
        $challenge = random_bytes(32);
        session()->put('webauthn.challenge', $challenge);

        // Create real objects
        $publicKeyCredential = new PublicKeyCredential(
            'public-key',
            'test-raw-id',
            new AuthenticatorAssertionResponse(
                Mockery::mock(CollectedClientData::class),
                Mockery::mock(AuthenticatorData::class),
                'signature',
                'test-user'
            )
        );

        // Mock the passkey
        $mockPasskeyCredential = Mockery::mock(PublicKeyCredentialSource::class);
        $mockPasskeyCredential->publicKeyCredentialId = 'test-raw-id';

        $mockPasskey = Mockery::mock(Passkey::class);
        $mockPasskey->shouldReceive('credential')->andReturn($mockPasskeyCredential);
        $mockPasskey->shouldReceive('setCredential')->andReturnSelf();
        $mockPasskey->shouldReceive('setLastLogin')->andReturnSelf();
        $mockPasskey->shouldReceive('save')->once();

        // Mock user passkeys
        $mockUser = Mockery::mock($user);
        $mockUser->shouldReceive('id')->andReturn('test-user');
        $mockUser->shouldReceive('passkeys->first')->andReturn($mockPasskey);

        $updatedCredentialSource = Mockery::mock(PublicKeyCredentialSource::class);

        $this->mockSerializer
            ->shouldReceive('deserialize')
            ->once()
            ->andReturn($publicKeyCredential);

        $this->mockAssertionValidator
            ->shouldReceive('check')
            ->once()
            ->andReturn($updatedCredentialSource);

        $result = $this->webauthn->validateAssertion($mockUser, $credentials);

        $this->assertTrue($result);
        $this->assertNull(session('webauthn.challenge')); // Challenge should be pulled
    }

    #[Test]
    public function it_throws_exception_when_no_matching_passkey()
    {
        $user = \Statamic\Facades\User::make()->id('test-user')->email('test@example.com');
        $user->save();

        $credentials = ['id' => 'credential-id', 'rawId' => 'raw-id', 'response' => [], 'type' => 'public-key'];
        session()->put('webauthn.challenge', random_bytes(32));

        $publicKeyCredential = new PublicKeyCredential(
            'public-key',
            'test-raw-id',
            new AuthenticatorAssertionResponse(
                Mockery::mock(CollectedClientData::class),
                Mockery::mock(AuthenticatorData::class),
                'signature',
                'test-user'
            )
        );

        $mockUser = Mockery::mock($user);
        $mockUser->shouldReceive('passkeys->first')->andReturn(null);

        $this->mockSerializer
            ->shouldReceive('deserialize')
            ->once()
            ->andReturn($publicKeyCredential);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No matching passkey found');

        $this->webauthn->validateAssertion($mockUser, $credentials);
    }
}
