<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Facades\User;
use Tests\Facades\Concerns\ProvidesExternalUrls;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use ProvidesExternalUrls;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    public function setUp(): void
    {
        parent::setUp();

        PasswordReset::resetFormUrl(null);

        $this->setSites([
            'a' => ['name' => 'A', 'locale' => 'en_US', 'url' => 'http://this-site.com/'],
            'b' => ['name' => 'B', 'locale' => 'en_US', 'url' => 'http://subdomain.this-site.com/'],
            'c' => ['name' => 'C', 'locale' => 'fr_FR', 'url' => '/fr/'],
        ]);
    }

    #[Test]
    public function it_accepts_encrypted_reset_url_when_sending_reset_link_email()
    {
        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => encrypt('http://this-site.com/some-path'),
        ])->assertSessionHasNoErrors();
        $this->assertEquals('http://this-site.com/some-path?token=test-token', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    public function it_accepts_unencrypted_relative_reset_url_when_sending_reset_link_email()
    {
        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => '/some-path',
        ])->assertSessionHasNoErrors();
        $this->assertEquals('http://absolute-url-resolved-from-request.com/some-path?token=test-token', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    #[DataProvider('externalResetUrlProvider')]
    public function it_rejects_unencrypted_external_reset_url_when_sending_reset_link_email($url)
    {
        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => $url,
        ])->assertSessionHasNoErrors(); // Allow the notification to be sent, but without the bad url.
        $this->assertEquals('http://absolute-url-resolved-from-request.com/!/auth/password/reset/test-token?', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    public function it_rejects_unencrypted_absolute_internal_reset_url_when_sending_reset_link_email()
    {
        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => 'http://this-site.com/some-path',
        ])->assertSessionHasNoErrors();
        $this->assertEquals('http://absolute-url-resolved-from-request.com/!/auth/password/reset/test-token?', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    public function it_rejects_unencrypted_relative_reset_url_with_control_characters_when_sending_reset_link_email()
    {
        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => "/some-path\r\nLocation: https://evil.com",
        ])->assertSessionHasNoErrors();
        $this->assertEquals('http://absolute-url-resolved-from-request.com/!/auth/password/reset/test-token?', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    public function it_rejects_reset_url_longer_than_2048_characters_when_sending_reset_link_email()
    {
        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => '/'.str_repeat('a', 2048),
        ])->assertSessionHasNoErrors();
        $this->assertEquals('http://absolute-url-resolved-from-request.com/!/auth/password/reset/test-token?', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    public function it_rejects_unencrypted_string_reset_url_when_sending_reset_link_email()
    {
        // Unencrypted string that doesn't look like a URL is probably a tampered encrypted string.
        // It might be a relative url without a leading slash, but we won't treat it as that.

        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => 'not-an-encrypted-string',
        ])->assertSessionHasNoErrors(); // Allow the notification to be sent, but without the bad url.
        $this->assertEquals('http://absolute-url-resolved-from-request.com/!/auth/password/reset/test-token?', PasswordReset::url('test-token', 'resets'));
    }

    #[Test]
    #[DataProvider('externalResetUrlProvider')]
    public function it_rejects_encrypted_external_reset_url_when_sending_reset_link_email($url)
    {
        // It's weird to point to an external URL, even if you encrypt it yourself.
        // This is an additional safeguard.

        $this->simulateSuccessfulPasswordResetEmail();
        $this->createUser();

        $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => encrypt($url),
        ])->assertSessionHasNoErrors(); // Allow the notification to be sent, but without the bad url.
        $this->assertEquals('http://absolute-url-resolved-from-request.com/!/auth/password/reset/test-token?', PasswordReset::url('test-token', 'resets'));
    }

    public static function externalResetUrlProvider()
    {
        $keyFn = function ($key) {
            return is_null($key) ? 'null' : $key;
        };

        return collect(static::externalUrls())->mapWithKeys(fn ($url) => [$keyFn($url) => [$url]])->all();
    }

    private function createUser(): void
    {
        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();
    }

    protected function simulateSuccessfulPasswordResetEmail()
    {
        $success = new class
        {
            public function sendResetLink()
            {
                return Password::RESET_LINK_SENT;
            }
        };

        Password::shouldReceive('broker')->andReturn($success);
    }
}
