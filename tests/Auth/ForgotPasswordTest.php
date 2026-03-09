<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

        $this->setSites([
            'a' => ['name' => 'A', 'locale' => 'en_US', 'url' => 'http://this-site.com/'],
            'b' => ['name' => 'B', 'locale' => 'en_US', 'url' => 'http://subdomain.this-site.com/'],
            'c' => ['name' => 'C', 'locale' => 'fr_FR', 'url' => '/fr/'],
        ]);
    }

    #[Test]
    #[DataProvider('externalUrlProvider')]
    public function it_validates_reset_url_when_sending_reset_link_email($url, $isExternal)
    {
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
