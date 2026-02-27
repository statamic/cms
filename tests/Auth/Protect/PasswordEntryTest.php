<?php

namespace Tests\Auth\Protect;

use Facades\Statamic\Auth\Protect\Protectors\Password\Token;
use PHPUnit\Framework\Attributes\DataProvider;
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
            'reference' => 'entry::test',
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
            'reference' => 'entry::test',
        ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-password',
            ])
            ->assertRedirect('http://localhost/target-url')
            ->assertSessionHas('statamic:protect:password.passwords.scheme.password-scheme', 'the-password')
            ->assertSessionMissing('statamic:protect:password.tokens.test-token');
    }

    #[Test]
    #[DataProvider('localPasswordProvider')]
    public function it_allows_access_if_local_password_was_entered(
        $passwordFieldInContent,
        $submittedPassword,
    ) {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-scheme-password'],
            'field' => 'password',
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this->createPage('test', ['data' => ['protect' => 'password-scheme', 'password' => $passwordFieldInContent]]);

        $this->get('test')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
                'reference' => 'entry::test',
            ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => $submittedPassword,
            ])
            ->assertRedirect('http://localhost/test')
            ->assertSessionHas('statamic:protect:password.passwords.ref.entry::test', $submittedPassword)
            ->assertSessionMissing('statamic:protect:password.passwords.password-scheme')
            ->assertSessionMissing('statamic:protect:password.tokens.test-token');
    }

    public static function localPasswordProvider()
    {
        return [
            'string' => [
                'the-local-password',
                'the-local-password',
            ],
            'array with single value' => [
                ['the-local-password'],
                'the-local-password',
            ],
            'array with multiple values' => [
                ['first-local-password', 'second-local-password'],
                'second-local-password',
            ],
        ];
    }

    #[Test]
    public function it_prefers_the_local_password_over_the_scheme_password()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-scheme-password'],
            'field' => 'password',
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this->createPage('test', ['data' => ['protect' => 'password-scheme', 'password' => 'the-scheme-password']]);

        $this->get('test')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
                'reference' => 'entry::test',
            ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-scheme-password',
            ])
            ->assertRedirect('http://localhost/test')
            ->assertSessionHas('statamic:protect:password.passwords.ref.entry::test', 'the-scheme-password')
            ->assertSessionMissing('statamic:protect:password.passwords.password-scheme')
            ->assertSessionMissing('statamic:protect:password.tokens.test-token');
    }

    #[Test]
    public function it_can_use_the_same_local_password_multiple_times()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-scheme-password'],
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
                'reference' => 'entry::test',
            ]);

        $this
            ->post('/!/protect/password', [
                'token' => 'test-token',
                'password' => 'the-local-password',
            ])
            ->assertRedirect('http://localhost/test')
            ->assertSessionHas('statamic:protect:password.passwords.ref.entry::test', 'the-local-password');

        $this->get('test')->assertOk();

        $this->get('test-2')
            ->assertRedirect('http://localhost/!/protect/password?token=test-token')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test-2',
                'reference' => 'entry::test-2',
            ]);
    }
}
