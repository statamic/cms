<?php

namespace Tests\Tags\User;

use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LoginFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    /** @test */
    public function it_renders_form()
    {
        $output = $this->tag('{{ user:login_form }}{{ /user:login_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/login">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    /** @test */
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ user:login_form redirect="/logged-in" class="form" id="form" }}{{ /user:login_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/login" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="referer" value="/logged-in" />', $output);
    }

    /** @test */
    public function it_wont_log_user_in_and_renders_errors()
    {
        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'leya',
            ])
            ->assertLocation('/');

        $this->assertFalse(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEquals(['Invalid credentials.'], $errors[1]);
        $this->assertEmpty($success[1]);
    }

    /** @test */
    public function it_will_log_user_in_and_render_success()
    {
        $this->assertFalse(auth()->check());

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
            ])
            ->assertLocation('/');

        $this->assertTrue(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Login successful.'], $success[1]);
    }

    /** @test */
    public function it_will_log_user_in_and_follow_custom_redirect_with_success()
    {
        $this->assertFalse(auth()->check());

        User::make()
            ->email('san@holo.com')
            ->password('chewy')
            ->save();

        $this
            ->post('/!/auth/login', [
                'token' => 'test-token',
                'email' => 'san@holo.com',
                'password' => 'chewy',
                'referer' => '/login-successful',
            ])
            ->assertLocation('/login-successful');

        $this->assertTrue(auth()->check());

        $output = $this->tag(<<<'EOT'
{{ user:login_form }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /user:login_form }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Login successful.'], $success[1]);
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
}
