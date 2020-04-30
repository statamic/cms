<?php

namespace Tests\Tags\User;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RegisterFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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
        $output = $this->tag('{{ user:register_form redirect="/registered" class="form" id="form" }}{{ /user:register_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="referer" value="/registered" />', $output);
    }

    /** @test */
    public function it_renders_form_with_fields_array()
    {
        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:register_form }}
EOT
);

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Email Address</label><input type="email" name="email" value="">',
            '<label>Password</label><input type="password" name="password" value="">',
            '<label>Password Confirmation</label><input type="password" name="password_confirmation" value="">',
            '<label>Name</label><input type="text" name="name" value="">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    /** @test */
    public function it_renders_form_with_fields_array_and_custom_blueprint()
    {
        $this->useCustomBlueprint();

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:register_form }}
EOT
);

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Email Address</label><input type="email" name="email" value="">',
            '<label>Password</label><input type="password" name="password" value="">',
            '<label>Password Confirmation</label><input type="password" name="password_confirmation" value="">',
            '<label>Full Name</label><input type="text" name="name" value="">',
            '<label>Phone Number</label><input type="text" name="phone" value="">',
            '<label>Over 18 years of age?</label><input type="text" name="age" value="">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    /** @test */
    public function it_wont_register_user_and_renders_errors()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'token' => 'test-token',
            ])
            ->assertSessionHasErrors([
                'email',
                'password',
            ])
            ->assertLocation('/');

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $expected = [
            'The email field is required.',
            'The password field is required.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEmpty($success[1]);
    }

    /** @test */
    public function it_wont_register_user_and_renders_custom_validation_errors()
    {
        $this->useCustomBlueprint();

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                'password_confirmation' => 'chewy',
            ])
            ->assertSessionHasErrors([
                'password', // Should fail now because we've defined `min:8` as a rule.
                'age', // An extra `required` field that we added.
            ])
            ->assertLocation('/');

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $expected = [
            'The password must be at least 8 characters.',
            'The age field is required.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEmpty($success[1]);
    }

    /** @test */
    public function it_will_register_user_and_render_success()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'token' => 'test-token',
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
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Registration successful.'], $success[1]);
    }

    /** @test */
    public function it_will_register_user_and_follow_custom_redirect_with_success()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                'password_confirmation' => 'chewy',
                'referer' => '/registration-successful',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/registration-successful');

        $this->assertNotNull(User::findByEmail('san@holo.com'));
        $this->assertTrue(auth()->check());
        $this->assertEquals('san@holo.com', auth()->user()->email());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Registration successful.'], $success[1]);
    }

    /** @test */
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=login-successful');

        $expected = '<input type="hidden" name="referer" value="login-successful" />';

        $output = $this->tag('{{ user:login_form }}{{ /user:login_form }}');

        $this->assertStringNotContainsString($expected, $output);

        $output = $this->tag('{{ user:login_form allow_request_redirect="true" }}{{ /user:login_form }}');

        $this->assertStringContainsString($expected, $output);
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
