<?php

namespace Tests\Tags\User;

use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\NormalizesHtml;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PasswordFormTest extends TestCase
{
    use NormalizesHtml, PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    #[Test]
    public function it_renders_form()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $output = $this->tag('{{ user:password_form }}{{ /user:password_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/password">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $output = $this->tag('{{ user:password_form redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /user:password_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/password" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    #[Test]
    public function it_renders_form_with_redirects_to_anchor()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $output = $this->tag('{{ user:password_form redirect="#form" error_redirect="#form" }}{{ /user:password_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    #[Test]
    public function it_renders_form_with_fields_array()
    {
        $this->actingAs(User::make()
            ->email('test@example.com')
            ->data(['name' => 'Test User'])
            ->save());

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ user:password_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:password_form }}
EOT
        ));

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Current Password</label><input id="userpassword-form-current-password-field" type="password" name="current_password" value="">',
            '<label>Password</label><input id="userpassword-form-password-field" type="password" name="password" value="">',
            '<label>Password Confirmation</label><input id="userpassword-form-password-confirmation-field" type="password" name="password_confirmation" value="">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
    public function it_wont_update_password_and_renders_errors()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $this
            ->post('/!/auth/password', [
                'current_password' => '',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors([
                'current_password',
                'password',
            ], null, 'user.password');

        $output = $this->tag(<<<'EOT'
{{ user:password_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:password_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            'The current password field is required.',
            'The password field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    #[Test]
    public function it_wont_update_password_and_renders_errors_with_incorrect_password()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $this
            ->post('/!/auth/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHasErrors([
                'current_password',
            ], null, 'user.password');

        $output = $this->tag(<<<'EOT'
{{ user:password_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:password_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [[
            'validation.current_password',
        ], [
            'The password is incorrect.',
        ]];

        $this->assertEmpty($success[1]);
        $this->assertContains($errors[1], $expected);
        $this->assertContains($inlineErrors[1], $expected);
    }

    #[Test]
    public function it_will_update_password_and_render_success()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $this
            ->post('/!/auth/password', [
                'current_password' => 'mypassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHasNoErrors();

        $output = $this->tag(<<<'EOT'
{{ user:password_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:password_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $this->assertEquals(['Change successful.'], $success[1]);
        $this->assertEmpty($errors[1]);
        $this->assertEmpty($inlineErrors[1]);
    }

    #[Test]
    public function it_will_update_password_and_follow_custom_redirect_with_success()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $this
            ->post('/!/auth/password', [
                'current_password' => 'mypassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
                '_redirect' => '/password-successful',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/password-successful');

        $output = $this->tag(<<<'EOT'
{{ user:password_form }}
    <p class="success">{{ success }}</p>
{{ /user:password_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Change successful.'], $success[1]);
    }

    #[Test]
    public function it_wont_update_password_and_follow_custom_redirect_with_errors()
    {
        $this->actingAs(User::make()->password('mypassword')->save());

        $this
            ->post('/!/auth/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
                '_error_redirect' => '/password-error',
            ])
            ->assertSessionHasErrors([
                'current_password',
            ], null, 'user.password')
            ->assertLocation('/password-error');

        $output = $this->tag(<<<'EOT'
{{ user:password_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:password_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [[
            'validation.current_password',
        ], [
            'The password is incorrect.',
        ]];

        $this->assertEmpty($success[1]);
        $this->assertContains($errors[1], $expected);
        $this->assertContains($inlineErrors[1], $expected);
    }

    #[Test]
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=password-successful&error_redirect=registration-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="password-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="registration-failure" />';

        $output = $this->tag('{{ user:password_form }}{{ /user:password_form }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ user:password_form allow_request_redirect="true" }}{{ /user:password_form }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
    }

    #[Test]
    public function it_handles_precognitive_requests()
    {
        if (! method_exists($this, 'withPrecognition')) {
            $this->markTestSkipped();
        }

        $this->actingAs(User::make()->password('mypassword')->save());

        $response = $this
            ->withPrecognition()
            ->postJson('/!/auth/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_will_delete_any_password_reset_tokens_when_updating_password()
    {
        $user = tap(User::make()->email('hoff@statamic.com')->password('mypassword'))->save();

        $token = Password::createToken($user);

        $this->assertTrue(Password::tokenExists($user, $token));

        $this
            ->actingAs($user)
            ->post('/!/auth/password', [
                'current_password' => 'mypassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse(Password::tokenExists($user, $token));
    }
}
