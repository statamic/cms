<?php

namespace Tests\Auth\Protect;

class AuthenticationProtectionTest extends PageProtectionTestCase
{
    /** @test */
    public function redirects_to_login_page()
    {
        config(['statamic.protect.schemes.logged_in' => [
            'driver' => 'auth',
            'login_url' => '/login',
            'append_redirect' => true,
        ]]);

        $this
            ->requestPageProtectedBy('logged_in')
            ->assertRedirect('http://localhost/login?redirect=http://localhost/test');
    }

    /** @test */
    public function redirects_to_login_page_without_appending()
    {
        config(['statamic.protect.schemes.logged_in' => [
            'driver' => 'auth',
            'login_url' => '/login',
            'append_redirect' => false,
        ]]);

        $this
            ->requestPageProtectedBy('logged_in')
            ->assertRedirect('http://localhost/login');
    }

    /** @test */
    public function it_denies_if_no_login_url_is_defined()
    {
        config(['statamic.protect.schemes.logged_in' => [
            'driver' => 'auth',
            'login_url' => null,
            'append_redirect' => false,
        ]]);

        $this
            ->requestPageProtectedBy('logged_in')
            ->assertStatus(403);
    }
}
