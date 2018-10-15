<?php

namespace Tests\Auth\Protect;

use Facades\Statamic\Auth\Protect\Protectors\Password\Token;

class PasswordProtectionTest extends PageProtectionTestCase
{
    /** @test */
    function redirects_to_password_form_url_and_generates_token()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => ['test']
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertRedirect('http://localhost/password-entry?token=test-token')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
            ]);
    }

    /** @test */
    function allow_access_if_password_has_been_entered_for_that_scheme()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => ['the-password'],
        ]]);

        session()->put('statamic:protect:password.passwords.password-scheme', 'the-password');

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertOk();
    }

    /** @test */
    function denies_access_if_no_form_url_is_defined()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-password']
        ]]);

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertStatus(403);
    }

    /** @test */
    function denies_access_if_no_passwords_are_defined()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => []
        ]]);

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertStatus(403);
    }

    /** @test */
    function password_form_url_is_unprotected()
    {
        config(['statamic.routes.routes' => [
            '/password-entry' => 'password-entry'
        ]]);

        config(['statamic.protect.default' => 'password-scheme']);
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => ['test']
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->get('/password-entry')
            ->assertOk();
    }
}
