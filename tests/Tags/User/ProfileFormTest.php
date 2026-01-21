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

        preg_match_all($this->regex(), $output, $actual);

        $expected = [
            '<label>Name</label><input id="userprofile-form-name-field" type="text" name="name" value="Test User">',
            '<label>Email Address</label><input id="userprofile-form-email-field" type="email" name="email" value="test@example.com">',
        ];

        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
    public function it_renders_form_with_tabs_array_and_custom_blueprint()
    {
        $this->useCustomBlueprint();

        $this->actingAs(User::make()
            ->email('test@example.com')
            ->data(['name' => 'Test User', 'phone' => '12345'])
            ->save());

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ user:profile_form }}
    {{ tabs }}
        <h2 class="tab">{{ display }}</h2>
        {{ sections }}
            <h3 class="section">{{ display }}</h3>
            {{ fields }}
                <label>{{ display }}</label>{{ field }}
            {{ /fields }}
        {{ /sections }}
    {{ /tabs }}
{{ /user:profile_form }}
EOT
        ));

        preg_match_all($this->regex(), $output, $actual);

        $expected = [
            '<h2 class="tab">Main</h2>',
            '<h3 class="section">Account</h3>',
            '<label>Full Name</label><input id="userprofile-form-name-field" type="text" name="name" value="Test User">',
            '<label>Email Address</label><input id="userprofile-form-email-field" type="email" name="email" value="test@example.com">',
            '<h3 class="section">About you</h3>',
            '<label>Phone Number</label><input id="userprofile-form-phone-field" type="text" name="phone" value="12345">',
            '<label>Over 18 years of age?</label><input id="userprofile-form-age-field" type="text" name="age" value="" required>',
            '<h2 class="tab">Options</h2>',
            '<h3 class="section">Communication</h3>',
            '<label>Newsletter</label><label><input type="hidden" name="newsletter" value="0"><input id="userprofile-form-newsletter-field" type="checkbox" name="newsletter" value="1" checked></label>',
        ];
        $this->assertEquals($expected, $actual[0]);
    }

    #[Test]
    public function it_renders_form_with_sections_array_and_custom_blueprint()
    {
        $this->useCustomBlueprint();

        $this->actingAs(User::make()
            ->email('test@example.com')
            ->data(['name' => 'Test User', 'phone' => '12345'])
            ->save());

        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ user:profile_form }}
    {{ sections }}
        <h3 class="section">{{ display }}</h3>
        {{ fields }}
            <label>{{ display }}</label>{{ field }}
        {{ /fields }}
    {{ /sections }}
{{ /user:profile_form }}
EOT
        ));

        preg_match_all($this->regex(), $output, $actual);

        $expected = [
            '<h3 class="section">Account</h3>',
            '<label>Full Name</label><input id="userprofile-form-name-field" type="text" name="name" value="Test User">',
            '<label>Email Address</label><input id="userprofile-form-email-field" type="email" name="email" value="test@example.com">',
            '<h3 class="section">About you</h3>',
            '<label>Phone Number</label><input id="userprofile-form-phone-field" type="text" name="phone" value="12345">',
            '<label>Over 18 years of age?</label><input id="userprofile-form-age-field" type="text" name="age" value="" required>',
            '<h3 class="section">Communication</h3>',
            '<label>Newsletter</label><label><input type="hidden" name="newsletter" value="0"><input id="userprofile-form-newsletter-field" type="checkbox" name="newsletter" value="1" checked></label>',
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

        preg_match_all($this->regex(), $output, $actual);

        $expected = [
            '<label>Full Name</label><input id="userprofile-form-name-field" type="text" name="name" value="Test User">',
            '<label>Email Address</label><input id="userprofile-form-email-field" type="email" name="email" value="test@example.com">',
            '<label>Phone Number</label><input id="userprofile-form-phone-field" type="text" name="phone" value="12345">',
            '<label>Over 18 years of age?</label><input id="userprofile-form-age-field" type="text" name="age" value="" required>',
            '<label>Newsletter</label><label><input type="hidden" name="newsletter" value="0"><input id="userprofile-form-newsletter-field" type="checkbox" name="newsletter" value="1" checked></label>',
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
            'tabs' => [
                [
                    'handle' => 'main', // Default tab
                    'display' => 'Main',
                    'sections' => [
                        [
                            'handle' => 'account', // Basic information is grouped in a section
                            'display' => 'Account',
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
                            ],
                        ],
                        [
                            'handle' => 'about-you', // Extra fields are grouped in another section
                            'display' => 'About you',
                            'fields' => [
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
                        ],
                    ],
                ],
                [
                    'handle' => 'options', // Adding an extra tab
                    'display' => 'Options',
                    'sections' => [
                        [
                            'handle' => 'communication', // Last test section in the blueprint
                            'display' => 'Communication',
                            'fields' => [
                                [
                                    'handle' => 'newsletter', // Adding custom newsletter field.
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => 'Newsletter',
                                        'default' => 1,
                                    ],
                                ],
                            ],
                        ],
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

    private function regex(): string
    {
        return '/(?:<h[23].+>.+<\/h[23]>|<label>.+<\/label><input.+>|<label>.+<\/label><label><input.+><input.+><\/label>)+/U';
    }
}
