<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    #[Test]
    public function it_responds_with_a_401_when_requesting_json()
    {
        $this->getJson('/cp/anything')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function redirects_to_login_page()
    {
        $this->get('/cp/anything')->assertRedirect('/cp/auth/login');
    }

    #[Test]
    public function redirects_to_defined_login_page_when_auth_is_disabled()
    {
        config(['statamic.cp.auth' => ['enabled' => false, 'redirect_to' => '/my-login-page']]);

        $this->get('/cp/anything')->assertRedirect('/my-login-page');
    }

    #[Test]
    public function responds_with_401_when_auth_is_disabled_and_no_redirect_is_defined()
    {
        config(['statamic.cp.auth' => ['enabled' => false]]);

        $this->get('/cp/anything')->assertStatus(401);
    }
}
