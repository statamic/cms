<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    #[Test]
    #[DataProvider('externalProvider')]
    public function it_validates_reset_url_when_sending_reset_link_email($url, $isExternal)
    {
        $this->setSites([
            'a' => ['name' => 'A', 'locale' => 'en_US', 'url' => 'http://this-site.com/'],
            'b' => ['name' => 'B', 'locale' => 'en_US', 'url' => 'http://subdomain.this-site.com/'],
            'c' => ['name' => 'C', 'locale' => 'fr_FR', 'url' => '/fr/'],
        ]);

        $this->simulateSuccessfulPasswordResetEmail();

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $response = $this->post('/!/auth/password/email', [
            'email' => 'san@holo.com',
            '_reset_url' => $url,
        ]);

        if ($isExternal) {
            $response->assertSessionHasErrors(['_reset_url']);

            return;
        }

        $response->assertSessionHasNoErrors();
    }

    public static function externalProvider()
    {
        return [
            ['http://this-site.com', false],
            ['http://this-site.com?foo', false],
            ['http://this-site.com#anchor', false],
            ['http://this-site.com/', false],
            ['http://this-site.com/?foo', false],
            ['http://this-site.com/#anchor', false],

            ['http://that-site.com', true],
            ['http://that-site.com/', true],
            ['http://that-site.com/?foo', true],
            ['http://that-site.com/#anchor', true],
            ['http://that-site.com/some-slug', true],
            ['http://that-site.com/some-slug?foo', true],
            ['http://that-site.com/some-slug#anchor', true],

            ['http://subdomain.this-site.com', false],
            ['http://subdomain.this-site.com/', false],
            ['http://subdomain.this-site.com/?foo', false],
            ['http://subdomain.this-site.com/#anchor', false],
            ['http://subdomain.this-site.com/some-slug', false],
            ['http://subdomain.this-site.com/some-slug?foo', false],
            ['http://subdomain.this-site.com/some-slug#anchor', false],

            ['http://absolute-url-resolved-from-request.com', false],
            ['http://absolute-url-resolved-from-request.com/', false],
            ['http://absolute-url-resolved-from-request.com/?foo', false],
            ['http://absolute-url-resolved-from-request.com/?anchor', false],
            ['http://absolute-url-resolved-from-request.com/some-slug', false],
            ['http://absolute-url-resolved-from-request.com/some-slug?foo', false],
            ['http://absolute-url-resolved-from-request.com/some-slug#anchor', false],
            ['/', false],
            ['/?foo', false],
            ['/#anchor', false],
            ['/some-slug', false],
            ['?foo', false],
            ['#anchor', false],
            ['', false],
            [null, false],

            // External domain that starts with a valid domain.
            ['http://this-site.com.au', true],
            ['http://this-site.com.au/', true],
            ['http://this-site.com.au/?foo', true],
            ['http://this-site.com.au/#anchor', true],
            ['http://this-site.com.au/some-slug', true],
            ['http://this-site.com.au/some-slug?foo', true],
            ['http://this-site.com.au/some-slug#anchor', true],
            ['http://subdomain.this-site.com.au', true],
            ['http://subdomain.this-site.com.au/', true],
            ['http://subdomain.this-site.com.au/?foo', true],
            ['http://subdomain.this-site.com.au/#anchor', true],
            ['http://subdomain.this-site.com.au/some-slug', true],
            ['http://subdomain.this-site.com.au/some-slug?foo', true],
            ['http://subdomain.this-site.com.au/some-slug#anchor', true],
        ];
    }

    #[Test]
    public function it_allows_reset_url_for_current_request_domain_when_not_in_sites_config()
    {
        $this->setSites([
            'a' => ['name' => 'A', 'locale' => 'en_US', 'url' => 'http://this-site.com/'],
        ]);

        $this->simulateSuccessfulPasswordResetEmail();

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/password/email', [
                'email' => 'san@holo.com',
                '_reset_url' => 'http://absolute-url-resolved-from-request.com/some-slug',
            ])
            ->assertSessionHasNoErrors();
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
