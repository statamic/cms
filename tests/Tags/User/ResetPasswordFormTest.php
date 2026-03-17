<?php

namespace Tests\Tags\User;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResetPasswordFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:reset_password_form }}{{ /user:reset_password_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/password/reset">', $output);
    }

    #[Test]
    public function it_will_follow_custom_redirect_with_success()
    {
        $user = tap(User::make()->email('san@holo.com')->password('chewy'))->save();

        $token = Password::createToken($user);

        $this
            ->post('/!/auth/password/reset', [
                'token' => $token,
                'email' => 'san@holo.com',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
                'redirect' => '/password-reset-successful',
            ])
            ->assertLocation('/password-reset-successful');
    }

    #[Test]
    public function it_wont_follow_redirect_to_external_url()
    {
        $user = tap(User::make()->email('san@holo.com')->password('chewy'))->save();

        $token = Password::createToken($user);

        $this
            ->from('/reset-password')
            ->post('/!/auth/password/reset', [
                'token' => $token,
                'email' => 'san@holo.com',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
                'redirect' => 'https://external-site.com/phishing',
            ])
            ->assertLocation('/');
    }

    #[Test]
    public function it_wont_follow_redirect_to_external_url_on_error()
    {
        $this
            ->from('/reset-password')
            ->post('/!/auth/password/reset', [
                'token' => 'invalid-token',
                'email' => 'invalid-email',
                'password' => 'short',
                'password_confirmation' => 'short',
                '_error_redirect' => 'https://external-site.com/phishing',
            ])
            ->assertLocation('/reset-password');
    }

    #[Test]
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=password-reset-successful&error_redirect=password-reset-failure');

        $expectedRedirect = '<input type="hidden" name="redirect" value="password-reset-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="password-reset-failure" />';

        $output = $this->tag('{{ user:reset_password_form }}{{ /user:reset_password_form }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ user:reset_password_form allow_request_redirect="true" }}{{ /user:reset_password_form }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
    }
}
