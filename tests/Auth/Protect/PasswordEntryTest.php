<?php

namespace Tests\Auth\Protect;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordEntryTest extends TestCase
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
}
