<?php

namespace Tests\Tags\User;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Passkey;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Statamic\Notifications\ElevatedSessionVerificationCode;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class ElevatedSessionFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/test-elevated-form', function () {
                return Parse::template('{{ user:elevated_session_form }}content{{ /user:elevated_session_form }}', trusted: true);
            });
        });
    }

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_form()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();
        $this->actingAs($user);

        $output = $this->tag('{{ user:elevated_session_form }}{{ /user:elevated_session_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/elevated-session">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
        $this->assertStringEndsWith('</form>', $output);
    }

    #[Test]
    public function it_renders_form_with_params()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();
        $this->actingAs($user);

        $output = $this->tag('{{ user:elevated_session_form class="form" id="form" }}{{ /user:elevated_session_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/elevated-session" class="form" id="form">', $output);
    }

    #[Test]
    public function it_provides_method_context()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();
        $this->actingAs($user);

        $output = $this->tag('{{ user:elevated_session_form }}<p class="method">{{ method }}</p>{{ /user:elevated_session_form }}');

        preg_match('/<p class="method">(.+)<\/p>/U', $output, $matches);
        $this->assertEquals('password_confirmation', $matches[1]);
    }

    #[Test]
    public function it_provides_verification_code_method_for_passwordless_users()
    {
        Notification::fake();

        $user = User::make()->email('foo@bar.com');
        $user->save();
        $this->actingAs($user);

        $output = $this->tag('{{ user:elevated_session_form }}<p class="method">{{ method }}</p>{{ /user:elevated_session_form }}');

        preg_match('/<p class="method">(.+)<\/p>/U', $output, $matches);
        $this->assertEquals('verification_code', $matches[1]);
    }

    #[Test]
    public function it_provides_passkey_method_when_passwords_are_disabled()
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $user = User::make()->email('foo@bar.com');
        $user->save();

        $passkey = \Mockery::mock(Passkey::class);
        $passkey->shouldReceive('id')->andReturn('passkey-1');
        $user->setPasskeys(collect([$passkey]));

        $this->actingAs($user);

        $output = $this->tag('{{ user:elevated_session_form }}<p class="method">{{ method }}</p>{{ /user:elevated_session_form }}');

        preg_match('/<p class="method">(.+)<\/p>/U', $output, $matches);
        $this->assertEquals('passkey', $matches[1]);
    }

    #[Test]
    public function it_provides_resend_code_url()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();
        $this->actingAs($user);

        $output = $this->tag('{{ user:elevated_session_form }}<a href="{{ resend_code_url }}">Resend</a>{{ /user:elevated_session_form }}');

        $this->assertStringContainsString('href="http://localhost/!/auth/elevated-session/resend-code"', $output);
    }

    #[Test]
    public function it_sends_verification_code_when_method_is_verification_code()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc');

        $user = User::make()->email('foo@bar.com');
        $user->save();
        $this->actingAs($user);

        $this->tag('{{ user:elevated_session_form }}{{ /user:elevated_session_form }}');

        Notification::assertSentTo($user, ElevatedSessionVerificationCode::class, function ($notification) {
            return $notification->verificationCode === 'abc';
        });

        $this->assertEquals('abc', session()->get('statamic_elevated_session_verification_code')['code']);
    }

    #[Test]
    public function it_does_not_render_when_user_not_logged_in()
    {
        $output = $this->tag('{{ user:elevated_session_form }}content{{ /user:elevated_session_form }}');

        $this->assertEquals('', $output);
    }

    #[Test]
    public function it_fetches_form_data()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();
        $this->actingAs($user);

        $form = Statamic::tag('user:elevated_session_form')->fetch();

        $this->assertEquals('http://localhost/!/auth/elevated-session', $form['attrs']['action']);
        $this->assertEquals('POST', $form['attrs']['method']);
        $this->assertEquals('password_confirmation', $form['method']);
        $this->assertArrayHasKey('_token', $form['params']);
    }

    #[Test]
    public function it_can_confirm_with_password()
    {
        $this->freezeTime();

        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();

        $this
            ->actingAs($user)
            ->post('/!/auth/elevated-session', [
                'password' => 'secret',
            ])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function it_cannot_confirm_with_incorrect_password()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();

        $this
            ->actingAs($user)
            ->post('/!/auth/elevated-session', [
                'password' => 'wrong',
            ])
            ->assertSessionHasErrors(['password'], null, 'user.elevated_session')
            ->assertSessionMissing('statamic_elevated_session');
    }

    #[Test]
    public function it_can_confirm_with_verification_code()
    {
        $this->freezeTime();
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc123');

        $user = User::make()->email('foo@bar.com');
        $user->save();

        $this->actingAs($user);
        session()->sendElevatedSessionVerificationCode();

        $this
            ->actingAs($user)
            ->post('/!/auth/elevated-session', [
                'verification_code' => 'abc123',
            ])
            ->assertSessionHas('statamic_elevated_session', now()->timestamp);
    }

    #[Test]
    public function it_cannot_confirm_with_incorrect_verification_code()
    {
        Notification::fake();
        Str::createRandomStringsUsing(fn () => 'abc123');

        $user = User::make()->email('foo@bar.com');
        $user->save();

        $this->actingAs($user);
        session()->sendElevatedSessionVerificationCode();

        $this
            ->actingAs($user)
            ->post('/!/auth/elevated-session', [
                'verification_code' => 'wrong',
            ])
            ->assertSessionHasErrors(['verification_code'], null, 'user.elevated_session')
            ->assertSessionMissing('statamic_elevated_session');
    }

    #[Test]
    public function it_redirects_to_intended_url_after_confirmation()
    {
        $user = User::make()->email('foo@bar.com')->password('secret');
        $user->save();

        redirect()->setIntendedUrl('/intended-destination');

        $this
            ->actingAs($user)
            ->post('/!/auth/elevated-session', [
                'password' => 'secret',
            ])
            ->assertRedirect('/intended-destination');
    }
}
