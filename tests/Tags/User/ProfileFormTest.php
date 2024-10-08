<?php

namespace Tests\Tags\User;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\NormalizesHtml;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ProfileFormTest extends TestCase
{
    use NormalizesHtml, PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    #[Test]
    public function it_renders_form()
    {
        $this->actingAs(User::make()->save());

        $output = $this->tag('{{ user:profile_form }}{{ /user:profile_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/profile">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $this->actingAs(User::make()->save());

        $output = $this->tag('{{ user:profile_form redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /user:profile_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/profile" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    #[Test]
    public function it_renders_form_with_redirects_to_anchor()
    {
        $this->actingAs(User::make()->save());

        $output = $this->tag('{{ user:profile_form redirect="#form" error_redirect="#form" }}{{ /user:profile_form }}');

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
{{ user:profile_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:profile_form }}
EOT
        ));

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Name</label><input type="text" name="name" value="Test User">',
            '<label>Email Address</label><input type="email" name="email" value="test@example.com">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
    public function it_renders_form_with_fields_array_and_custom_blueprint()
    {
        $this->useCustomBlueprint();

        $this->actingAs(User::make()
            ->email('test@example.com')
            ->data(['name' => 'Test User', 'phone' => '12345'])
            ->save());

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ user:profile_form }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /user:profile_form }}
EOT
        ));

        preg_match_all('/<label>.+<\/label><input.+>/U', $output, $actual);

        $expected = [
            '<label>Full Name</label><input type="text" name="name" value="Test User">',
            '<label>Email Address</label><input type="email" name="email" value="test@example.com">',
            '<label>Phone Number</label><input type="text" name="phone" value="12345">',
            '<label>Over 18 years of age?</label><input type="text" name="age" value="" required>',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
    public function it_wont_update_user_and_renders_errors()
    {
        $this->actingAs(User::make()->save());

        $this
            ->post('/!/auth/profile', [
                'email' => '',
            ])
            ->assertSessionHasErrors([
                'email',
            ], null, 'user.profile');

        $output = $this->tag(<<<'EOT'
{{ user:profile_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:profile_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            'The Email Address field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    #[Test]
    public function it_will_update_user_and_render_success()
    {
        $this->actingAs(User::make()->save());

        $this
            ->post('/!/auth/profile', [
                'email' => 'san@holo.com',
            ])
            ->assertSessionHasNoErrors();

        $output = $this->tag(<<<'EOT'
{{ user:profile_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:profile_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $this->assertEquals(['Update successful.'], $success[1]);
        $this->assertEmpty($errors[1]);
        $this->assertEmpty($inlineErrors[1]);
    }

    #[Test]
    public function it_will_update_user_and_follow_custom_redirect_with_success()
    {
        $this->actingAs(User::make()->save());

        $this
            ->post('/!/auth/profile', [
                'email' => 'san@holo.com',
                '_redirect' => '/profile-successful',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/profile-successful');

        $output = $this->tag(<<<'EOT'
{{ user:profile_form }}
    <p class="success">{{ success }}</p>
{{ /user:profile_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Update successful.'], $success[1]);
    }

    #[Test]
    public function it_wont_update_user_and_follow_custom_redirect_with_errors()
    {
        $this->actingAs(User::make()->save());

        $this
            ->post('/!/auth/profile', [
                'email' => '',
                '_error_redirect' => '/profile-error',
            ])
            ->assertSessionHasErrors([
                'email',
            ], null, 'user.profile')
            ->assertLocation('/profile-error');

        $output = $this->tag(<<<'EOT'
{{ user:profile_form }}
    <p class="success">{{ success }}</p>
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    {{ fields }}
        <p class="inline-error">{{ error }}</p>
    {{ /fields }}
{{ /user:profile_form }}
EOT
        );

        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);
        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            'The Email Address field is required.',
        ];

        $this->assertEmpty($success[1]);
        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected, $inlineErrors[1]);
    }

    #[Test]
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=profile-successful&error_redirect=registration-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="profile-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="registration-failure" />';

        $output = $this->tag('{{ user:profile_form }}{{ /user:profile_form }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ user:profile_form allow_request_redirect="true" }}{{ /user:profile_form }}');

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
                    'handle' => 'email', // Field is included by default, but we're just implying field order here.
                    'field' => [],
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
    public function it_handles_precognitive_requests()
    {
        if (! method_exists($this, 'withPrecognition')) {
            $this->markTestSkipped();
        }

        $this->actingAs(User::make()->save());

        $response = $this
            ->withPrecognition()
            ->postJson('/!/auth/profile', [
                'some' => 'thing',
            ]);

        $response->assertStatus(422);
    }
}
