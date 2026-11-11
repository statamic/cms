<?php

namespace Tests\Forms\Captcha;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Captcha\Hcaptcha;
use Statamic\Forms\Captcha\Recaptcha;
use Statamic\Forms\Captcha\RecaptchaV3;
use Statamic\Forms\Captcha\Turnstile;
use Tests\TestCase;

class CaptchaDriversTest extends TestCase
{
    private const CONFIG = ['site_key' => 'site-key', 'secret_key' => 'secret-key'];

    public static function driverProvider()
    {
        return [
            'turnstile' => [Turnstile::class, 'https://challenges.cloudflare.com/turnstile/v0/siteverify', 'cf-turnstile-response'],
            'hcaptcha' => [Hcaptcha::class, 'https://api.hcaptcha.com/siteverify', 'h-captcha-response'],
            'recaptcha' => [Recaptcha::class, 'https://www.google.com/recaptcha/api/siteverify', 'g-recaptcha-response'],
        ];
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_verifies_a_token_with_the_provider($class, $endpoint, $field)
    {
        Http::fake([$endpoint => Http::response(['success' => true])]);

        $captcha = new $class(self::CONFIG);

        $this->assertTrue($captcha->verify('the-token', '127.0.0.1'));
        $this->assertEquals($field, $captcha->field());

        Http::assertSent(function (Request $request) use ($endpoint) {
            return $request->url() === $endpoint
                && $request->method() === 'POST'
                && $request->isForm()
                && $request['secret'] === 'secret-key'
                && $request['response'] === 'the-token'
                && $request['remoteip'] === '127.0.0.1';
        });
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_when_the_provider_says_so($class, $endpoint)
    {
        Http::fake([$endpoint => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        $this->assertFalse((new $class(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_when_success_isnt_exactly_true($class, $endpoint)
    {
        Http::fake([$endpoint => Http::response(['success' => 'true'])]);

        $this->assertFalse((new $class(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_when_the_provider_errors($class, $endpoint)
    {
        Http::fake([$endpoint => Http::response(['success' => true], 500)]);

        $this->assertFalse((new $class(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_when_the_provider_returns_something_other_than_json($class, $endpoint)
    {
        Http::fake([$endpoint => Http::response('<html>nope</html>')]);

        $this->assertFalse((new $class(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_when_the_provider_cant_be_reached($class, $endpoint)
    {
        Http::fake([$endpoint => fn () => throw new ConnectionException('Timed out')]);

        $this->assertFalse((new $class(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_without_a_request_when_there_is_no_token($class)
    {
        Http::fake();

        $captcha = new $class(self::CONFIG);

        $this->assertFalse($captcha->verify(null));
        $this->assertFalse($captcha->verify(''));

        Http::assertNothingSent();
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_fails_without_a_request_when_the_token_is_too_long($class, $endpoint)
    {
        Http::fake([$endpoint => Http::response(['success' => true])]);

        $captcha = new $class(self::CONFIG);

        $this->assertFalse($captcha->verify(str_repeat('a', 8193)));
        Http::assertNothingSent();

        $this->assertTrue($captcha->verify(str_repeat('a', 8192)));
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_leaves_out_the_ip_when_there_isnt_one($class, $endpoint)
    {
        Http::fake([$endpoint => Http::response(['success' => true])]);

        (new $class(self::CONFIG))->verify('the-token');

        Http::assertSent(fn (Request $request) => ! array_key_exists('remoteip', $request->data()));
    }

    #[Test]
    public function hcaptcha_sends_the_site_key()
    {
        Http::fake(['https://api.hcaptcha.com/siteverify' => Http::response(['success' => true])]);

        (new Hcaptcha(self::CONFIG))->verify('the-token');

        Http::assertSent(fn (Request $request) => $request['sitekey'] === 'site-key');
    }

    #[Test]
    public function it_renders_the_widgets()
    {
        $this->assertEquals(
            '<div class="cf-turnstile" data-captcha data-sitekey="site-key"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>',
            (new Turnstile(self::CONFIG))->widget()
        );

        $this->assertEquals(
            '<div class="h-captcha" data-captcha data-sitekey="site-key"></div><script src="https://js.hcaptcha.com/1/api.js" async defer></script>',
            (new Hcaptcha(self::CONFIG))->widget()
        );

        $this->assertEquals(
            '<div class="g-recaptcha" data-captcha data-sitekey="site-key"></div><script src="https://www.google.com/recaptcha/api.js" async defer></script>',
            (new Recaptcha(self::CONFIG))->widget()
        );
    }

    #[Test]
    public function it_escapes_the_site_key_in_the_widget()
    {
        $widget = (new Turnstile(['site_key' => '"><script>alert(1)</script>']))->widget();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $widget);
        $this->assertStringContainsString('data-sitekey="&quot;&gt;&lt;script&gt;', $widget);
    }

    #[Test]
    public function recaptcha_v3_passes_with_a_good_enough_score_for_the_right_action()
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.7, 'action' => 'submit',
        ])]);

        $this->assertTrue((new RecaptchaV3(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    public function recaptcha_v3_fails_with_a_low_score()
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.3, 'action' => 'submit',
        ])]);

        $this->assertFalse((new RecaptchaV3(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    public function recaptcha_v3_uses_the_configured_score()
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.3, 'action' => 'submit',
        ])]);

        $this->assertTrue((new RecaptchaV3([...self::CONFIG, 'score' => 0.2]))->verify('the-token'));
        $this->assertFalse((new RecaptchaV3([...self::CONFIG, 'score' => 0.9]))->verify('the-token'));
    }

    #[Test]
    public function recaptcha_v3_fails_without_a_score()
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'action' => 'submit',
        ])]);

        $this->assertFalse((new RecaptchaV3(self::CONFIG))->verify('the-token'));
    }

    #[Test]
    public function recaptcha_v3_fails_for_a_token_made_for_another_action()
    {
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.9, 'action' => 'login',
        ])]);

        $this->assertFalse((new RecaptchaV3(self::CONFIG))->verify('the-token'));
        $this->assertTrue((new RecaptchaV3([...self::CONFIG, 'action' => 'login']))->verify('the-token'));
    }

    #[Test]
    public function recaptcha_v3_falls_back_to_the_default_action_when_the_configured_one_is_invalid()
    {
        $captcha = new RecaptchaV3([...self::CONFIG, 'action' => 'not allowed!']);

        $this->assertEquals('submit', $captcha->action());
    }

    #[Test]
    public function recaptcha_v3_renders_a_script_safe_widget()
    {
        $widget = (new RecaptchaV3(['site_key' => "</script><script>alert('x')</script>"]))->widget();

        $this->assertStringContainsString('<input type="hidden" name="g-recaptcha-response" value="" data-captcha data-captcha-recaptcha>', $widget);
        $this->assertStringNotContainsString("<script>alert('x')</script>", $widget);
        // Only the two script tags the widget opens are closed, so the key can't break out of them.
        $this->assertEquals(2, substr_count($widget, '</script>'));
        $this->assertStringContainsString('{action:"submit"}', $widget);
    }

    #[Test]
    public function recaptcha_v3_waits_for_the_script_instead_of_submitting_without_a_token()
    {
        $widget = (new RecaptchaV3(self::CONFIG))->widget();

        $this->assertStringContainsString('if(input.value)return;event.preventDefault();grecaptcha.ready(', $widget);
        $this->assertStringContainsString('grecaptcha.ready=grecaptcha.ready||function(cb)', $widget);
    }
}
