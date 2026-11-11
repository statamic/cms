<?php

namespace Tests\Forms\Captcha;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Captcha as CaptchaContract;
use Statamic\Facades\Captcha;
use Statamic\Facades\Form;
use Statamic\Forms\Captcha\Hcaptcha;
use Statamic\Forms\Captcha\NullCaptcha;
use Statamic\Forms\Captcha\Recaptcha;
use Statamic\Forms\Captcha\RecaptchaV3;
use Statamic\Forms\Captcha\Turnstile;
use Tests\TestCase;

class CaptchaManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('driverProvider')]
    public function it_resolves_the_built_in_drivers($driver, $class)
    {
        config(['statamic.forms.captcha.driver' => $driver]);

        $this->assertInstanceOf($class, Captcha::driver());
    }

    public static function driverProvider()
    {
        return [
            'turnstile' => ['turnstile', Turnstile::class],
            'hcaptcha' => ['hcaptcha', Hcaptcha::class],
            'recaptcha' => ['recaptcha', Recaptcha::class],
            'recaptcha_v3' => ['recaptcha_v3', RecaptchaV3::class],
            'null' => ['null', NullCaptcha::class],
        ];
    }

    #[Test]
    public function it_uses_the_null_driver_when_no_driver_is_configured()
    {
        config(['statamic.forms.captcha.driver' => null]);

        $this->assertInstanceOf(NullCaptcha::class, Captcha::driver());
        $this->assertFalse(Captcha::enabled());
    }

    #[Test]
    public function it_throws_an_exception_for_an_unknown_driver()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Captcha driver [nope] is not defined.');

        config(['statamic.forms.captcha.driver' => 'nope']);

        Captcha::driver();
    }

    #[Test]
    public function it_passes_the_config_to_the_driver()
    {
        config(['statamic.forms.captcha' => [
            'driver' => 'turnstile',
            'site_key' => 'the-site-key',
            'secret_key' => 'the-secret-key',
        ]]);

        $this->assertStringContainsString('data-sitekey="the-site-key"', Captcha::driver()->widget());
    }

    #[Test]
    public function it_is_only_enabled_once_both_keys_are_set()
    {
        config(['statamic.forms.captcha' => ['driver' => 'turnstile', 'site_key' => null, 'secret_key' => null]]);
        $this->assertFalse(Captcha::enabled());

        config(['statamic.forms.captcha.site_key' => 'site']);
        $this->assertFalse(Captcha::enabled());

        config(['statamic.forms.captcha' => ['driver' => 'turnstile', 'site_key' => null, 'secret_key' => 'secret']]);
        $this->assertFalse(Captcha::enabled());

        config(['statamic.forms.captcha.site_key' => 'site']);
        $this->assertTrue(Captcha::enabled());
    }

    #[Test]
    public function it_is_not_enabled_with_the_null_driver_even_with_keys()
    {
        config(['statamic.forms.captcha' => ['driver' => 'null', 'site_key' => 'site', 'secret_key' => 'secret']]);

        $this->assertFalse(Captcha::enabled());
    }

    #[Test]
    public function custom_drivers_can_be_added()
    {
        $custom = new class implements CaptchaContract
        {
            public array $config = [];

            public function widget(): string
            {
                return '<div data-captcha>custom</div>';
            }

            public function verify(?string $token, ?string $ip = null): bool
            {
                return $token === 'ok';
            }

            public function field(): string
            {
                return 'custom-token';
            }
        };

        Captcha::extend('custom', function ($app, $config) use ($custom) {
            $custom->config = $config;

            return $custom;
        });

        config(['statamic.forms.captcha' => ['driver' => 'custom', 'site_key' => null, 'secret_key' => null, 'foo' => 'bar']]);

        $this->assertTrue(Captcha::enabled());
        $this->assertSame($custom, Captcha::driver());
        $this->assertEquals('bar', $custom->config['foo']);
        $this->assertEquals('custom', $custom->config['driver']);
    }

    #[Test]
    public function it_gets_the_captcha_for_a_form_that_uses_one()
    {
        config(['statamic.forms.captcha' => ['driver' => 'turnstile', 'site_key' => 'site', 'secret_key' => 'secret']]);

        $this->assertNull(Captcha::forForm(Form::make('contact')));
        $this->assertNull(Captcha::forForm(Form::make('contact')->data(['captcha' => false])));
        $this->assertInstanceOf(Turnstile::class, Captcha::forForm(Form::make('contact')->data(['captcha' => true])));
    }

    #[Test]
    public function it_doesnt_get_a_captcha_for_a_form_when_captchas_arent_set_up()
    {
        config(['statamic.forms.captcha' => ['driver' => 'turnstile', 'site_key' => null, 'secret_key' => null]]);

        $this->assertNull(Captcha::forForm(Form::make('contact')->data(['captcha' => true])));
    }
}
