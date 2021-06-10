<?php

namespace Tests\Tags\User;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\NormalizesHtml;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RegisterFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, NormalizesHtml;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    /** @test */
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:register_form }}{{ /user:register_form }}');
        $aliased = $this->tag('{{ user:registration_form }}{{ /user:registration_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register">', $output);
        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register">', $aliased);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $aliased);
        $this->assertStringEndsWith('</form>', $output);
        $this->assertStringEndsWith('</form>', $aliased);
    }

    /** @test */
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ user:register_form redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /user:register_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    /** @test */
    public function it_renders_form_with_redirects_to_anchor()
    {
        $output = $this->tag('{{ user:register_form redirect="#form" error_redirect="#form" }}{{ /user:register_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    /** @test */
    public function it_renders_form_with_fields_array()
    {
        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ user:register_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:register_form }}
EOT
));

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Email Address</label><input type="email" name="email">',
            '<label>Password</label><input type="password" name="password">',
            '<label>Password Confirmation</label><input type="password" name="password_confirmation">',
            '<label>Name</label><input type="text" name="name">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    /** @test */
    public function it_renders_form_with_fields_array_and_custom_blueprint()
    {
        $this->useCustomBlueprint();

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ user:register_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:register_form }}
EOT
));

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Email Address</label><input type="email" name="email">',
            '<label>Password</label><input type="password" name="password">',
            '<label>Password Confirmation</label><input type="password" name="password_confirmation">',
            '<label>Full Name</label><input type="text" name="name">',
            '<label>Phone Number</label><input type="text" name="phone">',
            '<label>Over 18 years of age?</label><input type="text" name="age" required>',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    /** @test */
    public function it_wont_register_user_and_renders_errors()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [])
            ->assertSessionHasErrors([
                'email',
                'password',
            ], null, 'user.register')
            ->assertLocation('/');

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            'The email field is required.',
            'The password field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    /** @test */
    public function it_wont_register_user_and_renders_custom_validation_errors()
    {
        $this->useCustomBlueprint();

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewy',
                'password_confirmation' => 'chewy',
            ])
            ->assertSessionHasErrors([
                'password', // Should fail now because we've defined `min:8` as a rule.
                'age', // An extra `required` field that we added.
            ], null, 'user.register')
            ->assertLocation('/');

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
    <p class="age-error">{{ error:age }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="age-error">(.+)<\/p>/U', $output, $ageError);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        // TODO: It seems
        $expected = [
            'The password must be at least 8 characters.',
            'The age field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected[1], $errors[1][1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    /** @test */
    public function it_will_register_user_and_render_success()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewy',
                'password_confirmation' => 'chewy',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertNotNull(User::findByEmail('san@holo.com'));
        $this->assertTrue(auth()->check());
        $this->assertEquals('san@holo.com', auth()->user()->email());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $this->assertEquals(['Registration successful.'], $success[1]);
        $this->assertEmpty($errors[1]);
        $this->assertEmpty($inlineErrors[1]);
    }

    /** @test */
    public function it_will_register_user_and_follow_custom_redirect_with_success()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewy',
                'password_confirmation' => 'chewy',
                '_redirect' => '/registration-successful',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/registration-successful');

        $this->assertNotNull(User::findByEmail('san@holo.com'));
        $this->assertTrue(auth()->check());
        $this->assertEquals('san@holo.com', auth()->user()->email());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Registration successful.'], $success[1]);
    }

    /** @test */
    public function it_wont_register_user_and_follow_custom_redirect_with_errors()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                '_error_redirect' => '/registration-error',
            ])
            ->assertSessionHasErrors([
                'email',
                'password',
            ], null, 'user.register')
            ->assertLocation('/registration-error');

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            'The email field is required.',
            'The password field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    /** @test */
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=registration-successful&error_redirect=registration-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="registration-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="registration-failure" />';

        $output = $this->tag('{{ user:register_form }}{{ /user:register_form }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ user:register_form allow_request_redirect="true" }}{{ /user:register_form }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
    }

    private function useCustomBlueprint()
    {
        $blueprint = Blueprint::make()->setContents([
            'fields' => [
                [
                    'handle' => 'name', // Field already exists, but we're defining custom display string.
                    'field' => [
                        'type' => 'text',
                        'display' => 'Full Name',
                    ],
                ],
                [
                    'handle' => 'password', // Field already exists, but we're defining custom validation rules.
                    'field' => [
                        'type' => 'text',
                        'input_type' => 'password',
                        'display' => 'Password',
                        'validate' => 'min:8',
                    ],
                ],
                [
                    'handle' => 'phone', // Adding custom phone field.
                    'field' => [
                        'type' => 'text',
                        'display' => 'Phone Number',
                    ],
                ],
                [
                    'handle' => 'age', // Adding custom age field.
                    'field' => [
                        'type' => 'text',
                        'display' => 'Over 18 years of age?',
                        'validate' => 'required',
                    ],
                ],
            ],
        ]);

        Blueprint::shouldReceive('find')
            ->with('user')
            ->andReturn($blueprint);
    }
}
