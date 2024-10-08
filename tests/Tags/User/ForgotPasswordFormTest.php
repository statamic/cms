<?php

namespace Tests\Tags\User;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ForgotPasswordFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    #[Test]
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:forgot_password_form }}{{ /user:forgot_password_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/password/email">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ user:forgot_password_form redirect="/submitted" error_redirect="/errors" reset_url="/resetting" class="form" id="form" }}{{ /user:forgot_password_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/password/email" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_reset_url" value="/resetting" />', $output);
    }

    #[Test]
    public function it_renders_form_with_redirects_to_anchor()
    {
        $output = $this->tag('{{ user:forgot_password_form redirect="#form" error_redirect="#form" }}{{ /user:forgot_password_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    #[Test]
    public function it_wont_send_reset_link_for_non_existent_user_and_renders_errors()
    {
        $this
            ->post('/!/auth/password/email', [
                'email' => 'san@holo.com',
            ])
            ->assertLocation('/');

        $output = $this->tag(<<<'EOT'
{{ user:forgot_password_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
    <p class="email_sent">{{ email_sent }}</p>
{{ /user:forgot_password_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="email_sent">(.+)<\/p>/U', $output, $emailSent);

        $this->assertEquals([__(Password::INVALID_USER)], $errors[1]);
        $this->assertEmpty($success[1]);
        $this->assertEmpty($emailSent[1]);
    }

    #[Test]
    public function it_wont_send_reset_link_for_invalid_email_and_renders_errors()
    {
        $this
            ->post('/!/auth/password/email', [
                'email' => 'test',
            ])
            ->assertLocation('/');

        $output = $this->tag(<<<'EOT'
{{ user:forgot_password_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
    <p class="email_sent">{{ email_sent }}</p>
{{ /user:forgot_password_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="email_sent">(.+)<\/p>/U', $output, $emailSent);

        $this->assertEquals([__('validation.email', ['attribute' => 'email'])], $errors[1]);
        $this->assertEmpty($success[1]);
        $this->assertEmpty($emailSent[1]);
    }

    #[Test]
    public function it_will_send_password_reset_email_and_render_success()
    {
        $this->simulateSuccessfulPasswordResetEmail();

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/password/email', [
                'email' => 'san@holo.com',
            ])
            ->assertLocation('/');

        $output = $this->tag(<<<'EOT'
{{ user:forgot_password_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}

    <p class="success">{{ success }}</p>
    <p class="email_sent">{{ email_sent }}</p>
{{ /user:forgot_password_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="email_sent">(.+)<\/p>/U', $output, $emailSent);

        $this->assertEmpty($errors[1]);
        $this->assertEquals([__(Password::RESET_LINK_SENT)], $success[1]);
        $this->assertEquals([__(Password::RESET_LINK_SENT)], $emailSent[1]);
    }

    #[Test]
    public function it_will_send_password_reset_email_and_follow_custom_redirect_with_success()
    {
        $this->simulateSuccessfulPasswordResetEmail();

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/password/email', [
                'email' => 'san@holo.com',
                '_redirect' => '/password-reset-successful',
            ])
            ->assertLocation('/password-reset-successful');

        $output = $this->tag(<<<'EOT'
{{ user:forgot_password_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}

    <p class="success">{{ success }}</p>
    <p class="email_sent">{{ email_sent }}</p>
{{ /user:forgot_password_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="email_sent">(.+)<\/p>/U', $output, $emailSent);

        $this->assertEmpty($errors[1]);
        $this->assertEquals([__(Password::RESET_LINK_SENT)], $success[1]);
        $this->assertEquals([__(Password::RESET_LINK_SENT)], $emailSent[1]);
    }

    #[Test]
    public function it_wont_log_user_in_and_follow_custom_error_redirect_with_errors()
    {
        $this
            ->post('/!/auth/password/email', [
                'email' => 'san@holo.com',
                '_error_redirect' => '/password-reset-error',
            ])
            ->assertLocation('/password-reset-error');

        $output = $this->tag(<<<'EOT'
{{ user:forgot_password_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
    <p class="email_sent">{{ email_sent }}</p>
{{ /user:forgot_password_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="email_sent">(.+)<\/p>/U', $output, $emailSent);

        $this->assertEquals([__(Password::INVALID_USER)], $errors[1]);
        $this->assertEmpty($success[1]);
        $this->assertEmpty($emailSent[1]);
    }

    #[Test]
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=password-reset-successful&error_redirect=password-reset-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="password-reset-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="password-reset-failure" />';

        $output = $this->tag('{{ user:forgot_password_form }}{{ /user:forgot_password_form }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ user:forgot_password_form allow_request_redirect="true" }}{{ /user:forgot_password_form }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
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

    #[Test]
    public function it_fetches_form_data()
    {
        $form = Statamic::tag('user:forgot_password_form')->fetch();

        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/auth/password/email');
        $this->assertEquals($form['attrs']['method'], 'POST');

        $this->assertArrayHasKey('_token', $form['params']);
    }
}
