<?php

namespace Tests\Auth\Protect;

use Facades\Statamic\Auth\Protect\Protectors\Password\Token;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

class PasswordProtectionTest extends PageProtectionTestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Route::view('/password-entry', 'password-entry');
    }

    #[Test]
    public function redirects_to_password_form_url_and_generates_token()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['test'],
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertRedirect('http://localhost/!/protect/password?token=test-token')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/test',
                'reference' => 'entry::test',
            ]);
    }

    #[Test]
    public function redirects_to_password_form_url_and_generates_token_on_a_404_url()
    {
        config()->set('statamic.protect.default', 'password-scheme');
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['test'],
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->get('this-page-does-not-exist')
            ->assertRedirect('http://localhost/!/protect/password?token=test-token')
            ->assertSessionHas('statamic:protect:password.tokens.test-token', [
                'scheme' => 'password-scheme',
                'url' => 'http://localhost/this-page-does-not-exist',
                'reference' => null,
            ]);
    }

    #[Test]
    public function password_form_url_can_be_overridden()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['test'],
            'form_url' => '/password-entry',
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertRedirect('http://localhost/password-entry?token=test-token');
    }

    #[Test]
    public function allow_access_if_password_has_been_entered_for_that_scheme()
    {
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['the-password'],
        ]]);

        session()->put('statamic:protect:password.passwords.scheme.password-scheme', 'the-password');

        $this
            ->requestPageProtectedBy('password-scheme')
            ->assertOk();
    }

    #[Test]
    public function default_password_form_url_is_unprotected()
    {
        $this->viewShouldReturnRendered('statamic::auth.protect.password', '');

        config(['statamic.protect.default' => 'password-scheme']);
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'allowed' => ['test'],
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->get('/!/protect/password')
            ->assertOk();
    }

    #[Test]
    public function custom_password_form_url_is_unprotected()
    {
        $this->viewShouldReturnRendered('password-entry', 'Password form template');

        config(['statamic.protect.default' => 'password-scheme']);
        config(['statamic.protect.schemes.password-scheme' => [
            'driver' => 'password',
            'form_url' => '/password-entry',
            'allowed' => ['test'],
        ]]);

        Token::shouldReceive('generate')->andReturn('test-token');

        $this
            ->get('/password-entry')
            ->assertOk()
            ->assertSee('Password form template');
    }
}
