<?php

namespace Tests\Tags\User;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Parse;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Statamic;
use Tests\NormalizesHtml;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RegisterFormTest extends TestCase
{
    use NormalizesHtml, PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    #[Test]
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:register_form }}{{ /user:register_form }}');
        $aliased = $this->tag('{{ user:registration_form }}{{ /user:registration_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register">', $output);
        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register">', $aliased);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringContainsString(csrf_field(), $aliased);
        $this->assertStringEndsWith('</form>', $output);
        $this->assertStringEndsWith('</form>', $aliased);
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ user:register_form redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /user:register_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/register" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    #[Test]
    public function it_renders_form_with_redirects_to_anchor()
    {
        $output = $this->tag('{{ user:register_form redirect="#form" error_redirect="#form" }}{{ /user:register_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    #[Test]
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
            '<label>Email Address</label><input id="userregister-form-email-field" type="email" name="email" value="">',
            '<label>Password</label><input id="userregister-form-password-field" type="password" name="password" value="">',
            '<label>Password Confirmation</label><input id="userregister-form-password_confirmation-field" type="password" name="password_confirmation" value="">',
            '<label>Name</label><input id="userregister-form-name-field" type="text" name="name" value="">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
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
            '<label>Email Address</label><input id="userregister-form-email-field" type="email" name="email" value="">',
            '<label>Password</label><input id="userregister-form-password-field" type="password" name="password" value="">',
            '<label>Password Confirmation</label><input id="userregister-form-password_confirmation-field" type="password" name="password_confirmation" value="">',
            '<label>Full Name</label><input id="userregister-form-name-field" type="text" name="name" value="">',
            '<label>Phone Number</label><input id="userregister-form-phone-field" type="text" name="phone" value="">',
            '<label>Over 18 years of age?</label><input id="userregister-form-age-field" type="text" name="age" value="" required>',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
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
            'The Email Address field is required.',
            'The Password field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    #[Test]
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

        $expected = [
            trans('validation.min.string', ['attribute' => 'Password', 'min' => 8]), // 'The password must be at least 8 characters.',
            trans('validation.required', ['attribute' => 'Over 18 years of age?']), // 'The age field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected[1], $errors[1][1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    #[Test]
    public function it_will_register_user_and_render_success()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewbacca',
                'password_confirmation' => 'chewbacca',
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

    #[Test]
    public function it_will_register_user_and_follow_custom_redirect_with_success()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewbacca',
                'password_confirmation' => 'chewbacca',
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

    #[Test]
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
            'The Email Address field is required.',
            'The Password field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    #[Test]
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

    #[Test]
    public function it_ensures_some_fields_arent_saved()
    {
        UserGroup::make('client')->title('Client')->save();
        Role::make('admin')->title('Admin')->save();

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewbacca',
                'password_confirmation' => 'chewbacca',
                'groups' => ['client'],
                'roles' => ['admin'],
                'super' => true,
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $user = User::findByEmail('san@holo.com');

        $this->assertEquals($user->groups()->count(), 0);
        $this->assertEquals($user->roles()->count(), 0);
        $this->assertNull($user->get('super'));
        $this->assertNull($user->get('password_confirmation'));
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

    #[Test]
    public function it_fetches_form_data()
    {
        $form = Statamic::tag('user:register_form')->fetch();

        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/auth/register');
        $this->assertEquals($form['attrs']['method'], 'POST');

        $this->assertArrayHasKey('_token', $form['params']);
    }

    #[Test]
    public function it_wont_register_user_when_honeypot_is_present()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        config()->set('statamic.users.registration_form_honeypot_field', 'honeypot');

        $response = $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewbacca',
                'password_confirmation' => 'chewbacca',
                'honeypot' => 'falcon',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Registration successful.'], $success[1]);

        config()->set('statamic.users.registration_form_honeypot_field', null);
    }

    #[Test]
    public function it_will_register_user_when_honeypot_is_not_present()
    {
        $this->assertNull(User::findByEmail('san@holo.com'));
        $this->assertFalse(auth()->check());

        config()->set('statamic.users.registration_form_honeypot_field', 'honeypot');

        $response = $this
            ->post('/!/auth/register', [
                'email' => 'san@holo.com',
                'password' => 'chewbacca',
                'password_confirmation' => 'chewbacca',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertNotNull(User::findByEmail('san@holo.com'));
        $this->assertTrue(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:register_form }}
    <p class="success">{{ success }}</p>
{{ /user:register_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Registration successful.'], $success[1]);

        config()->set('statamic.users.registration_form_honeypot_field', null);
    }

    #[Test]
    public function it_handles_precognitive_requests()
    {
        if (! method_exists($this, 'withPrecognition')) {
            $this->markTestSkipped();
        }

        $response = $this
            ->withPrecognition()
            ->postJson('/!/auth/register', [
                'password_confirmation' => 'no',
            ]);

        $response->assertStatus(422);
    }
}
