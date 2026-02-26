<?php

namespace Tests\OAuth;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Http\Controllers\OAuthController;
use Tests\TestCase;

class OAuthRedirectTest extends TestCase
{
    #[Test]
    public function it_redirects_to_local_url()
    {
        session(['_previous.url' => 'http://localhost/oauth/test?redirect=/dashboard']);

        $this->assertEquals('/dashboard', $this->getSuccessRedirectUrl());
    }

    #[Test]
    public function it_does_not_redirect_to_external_url()
    {
        session(['_previous.url' => 'http://localhost/oauth/test?redirect=https://evil.com']);

        $this->assertEquals('/', $this->getSuccessRedirectUrl());
    }

    /**
     * The successRedirectUrl() method is protected, so we need to new up a fake class to call it.
     */
    private function getSuccessRedirectUrl(): string
    {
        $controller = new class extends OAuthController
        {
            public function getSuccessRedirectUrl()
            {
                return $this->successRedirectUrl();
            }
        };

        return $controller->getSuccessRedirectUrl();
    }
}
