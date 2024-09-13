<?php

namespace Tests\Auth\Protect;

use Facades\Statamic\Auth\Protect\Protectors\Password\Token;
use PHPUnit\Framework\Attributes\Test;

class PasswordEntryTest extends PageProtectionTestCase
{
    #[Test]
    public function it_returns_back_with_error_if_theres_no_token()
    {
        $this
            ->from('/original')
            ->post('/!/protect/password', [
                'password' => 'test',
            ])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('token', null, 'passwordProtect');
    }

    #[Test]
    public function it_returns_back_with_error_if_the_wrong_password_is_entered()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => ['the-password'],
        ]]);

        session()->put('statamic:protect:password.tokens.test-token', [
            'scheme' => 'password-scheme',
            'url' => '/target-url',
            'id' => 'test',
            'valid_passwords' => ['the-password'],
            'local_password' => null,
        ]);

        $this
            ->from('/original')
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('password', null, 'passwordProtect');
    }

    #[Test]
    public function it_allows_access_if_allowed_password_was_entered()
    {
        $this->withoutExceptionHandling();

        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => ['the-password'],
        ]]);

        session()->put('statamic:protect:password.tokens.test-token', [
            'scheme' => 'password-scheme',
            'url' => '/target-url',
            'id' => 'test',
            'valid_passwords' => ['the-password'],
            'local_password' => null,
        ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-password',
            ])
            ->assertRedirect('http://localhost/target-url')
            ->assertSessionHas('statamic:protect:password.passwords.password-scheme', 'the-password')
            ->assertSessionMissing('statamic:protect:password.tokens.test-token');
    }

    #[Test]
    public function it_allows_access_if_local_password_was_entered()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-master-password'],
            'field' => 'password',
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this->createPage('test', ['data' => ['protect' => 'password-scheme', 'password' => 'the-local-password']]);

        $this->get('test')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
                'id' => 'test',
                'valid_passwords' => ['the-master-password', 'the-local-password'],
                'local_password' => 'the-local-password',
            ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-local-password',
            ])
            ->assertRedirect('http://localhost/test')
            ->assertSessionHas('statamic:protect:password.passwords.test', 'the-local-password')
            ->assertSessionMissing('statamic:protect:password.passwords.password-scheme')
            ->assertSessionMissing('statamic:protect:password.tokens.test-token');
    }

    #[Test]
    public function it_prefers_the_local_password_over_the_master_password()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-master-password'],
            'field' => 'password',
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this->createPage('test', ['data' => ['protect' => 'password-scheme', 'password' => 'the-master-password']]);

        $this->get('test')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
                'id' => 'test',
                'valid_passwords' => ['the-master-password'],
                'local_password' => 'the-master-password',
            ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-master-password',
            ])
            ->assertRedirect('http://localhost/test')
            ->assertSessionHas('statamic:protect:password.passwords.test', 'the-master-password')
            ->assertSessionMissing('statamic:protect:password.passwords.password-scheme')
            ->assertSessionMissing('statamic:protect:password.tokens.test-token');
    }

    #[Test]
    public function it_can_use_the_same_local_password_multiple_times()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-master-password'],
            'field' => 'password',
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this->createPage('test', ['data' => ['protect' => 'password-scheme', 'password' => 'the-local-password']]);
        $this->createPage('test-2', ['data' => ['protect' => 'password-scheme', 'password' => 'the-local-password']]);

        $this->get('test')
            ->assertRedirect('http://localhost/!/protect/password?token=test-token')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
                'id' => 'test',
                'valid_passwords' => ['the-master-password', 'the-local-password'],
                'local_password' => 'the-local-password',
            ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-local-password',
            ])
            ->assertRedirect('http://localhost/test')
            ->assertSessionHas('statamic:protect:password.passwords.test', 'the-local-password');

        $this->get('test')->assertOk();

        $this->get('test-2')
            ->assertRedirect('http://localhost/!/protect/password?token=test-token')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test-2',
                'id' => 'test-2',
                'valid_passwords' => ['the-master-password', 'the-local-password'],
                'local_password' => 'the-local-password',
            ]);
    }
}
