<?php

namespace Statamic\Forms\Captcha;

use Statamic\Contracts\Forms\Captcha;
use Statamic\Contracts\Forms\Form;
use Statamic\Support\Manager;

class CaptchaManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->app['config']['statamic.forms.captcha.driver'] ?: 'null';
    }

    protected function getConfig($name)
    {
        return array_merge($this->app['config']['statamic.forms.captcha'] ?? [], ['driver' => $name]);
    }

    protected function invalidImplementationMessage($name)
    {
        return "Captcha driver [{$name}] is not defined.";
    }

    protected function invalidDriverMessage($driver, $name)
    {
        return "Captcha driver [{$driver}] is not defined.";
    }

    /**
     * Whether a captcha has been set up. The built in drivers need both keys,
     * so they stay off until both are set. Custom drivers decide for themselves
     * what they need, so they're on as soon as they're the configured driver.
     */
    public function enabled(): bool
    {
        $driver = $this->getDefaultDriver();

        if ($driver === 'null') {
            return false;
        }

        if (isset($this->customCreators[$driver])) {
            return true;
        }

        $config = $this->getConfig($driver);

        return filled($config['site_key'] ?? null) && filled($config['secret_key'] ?? null);
    }

    /**
     * The captcha a form should use, or null when it shouldn't use one.
     */
    public function forForm(Form $form): ?Captcha
    {
        if (! $form->get('captcha') || ! $this->enabled()) {
            return null;
        }

        return $this->driver();
    }

    public function createNullDriver()
    {
        return new NullCaptcha;
    }

    public function createTurnstileDriver(array $config)
    {
        return new Turnstile($config);
    }

    public function createHcaptchaDriver(array $config)
    {
        return new Hcaptcha($config);
    }

    public function createRecaptchaDriver(array $config)
    {
        return new Recaptcha($config);
    }

    public function createRecaptchaV3Driver(array $config)
    {
        return new RecaptchaV3($config);
    }
}
