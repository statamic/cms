<?php

namespace Statamic;

use Statamic\Facades\Site;

/**
 * Interacting with the configuration.
 */
class Config
{
    /**
     * Get a config variable.
     *
     * @param  string  $key  The name of the key
     * @param  mixed|bool  $default  The fallback value
     * @return mixed
     */
    public function get(string $key, $default = false)
    {
        return config($key, $default);
    }

    /**
     * Set a config variable.
     *
     * @param  string  $key  The name of the key
     * @param  mixed  $value  The value to set
     */
    public function set(string $key, mixed $value)
    {
        config([$key => $value]);
    }

    /**
     * Get all config values.
     *
     * @return array
     */
    public function all()
    {
        return config()->all();
    }

    /**
     * Get the app key.
     */
    public function getAppKey(): string
    {
        return $this->get('app.key');
    }

    /**
     * Get the license key.
     */
    public function getLicenseKey(): ?string
    {
        $key = $this->get('statamic.system.license_key');

        if (! $key || $key == '') {
            return null;
        }

        return $key;
    }

    public function getSite($locale = null): \Statamic\Sites\Site
    {
        return Site::get($locale ?? Site::current()->handle());
    }

    /**
     * Get the current locale's full code for date string translations.
     *
     * @param  string|null  $locale
     */
    public function getFullLocale($locale = null): string
    {
        return $this->getSite($locale)->locale();
    }

    /**
     * Get the current locale's short code.
     *
     * @param  string|null  $locale
     * @return string
     */
    public function getShortLocale($locale = null)
    {
        return $this->getSite($locale)->shortLocale();
    }

    /**
     * Get the current locale's name.
     *
     * @param  string|null  $locale
     * @return string
     */
    public function getLocaleName($locale = null)
    {
        return $this->getSite($locale)->name();
    }

    /**
     * Get the locale keys.
     *
     * @return array
     */
    public function getLocales()
    {
        return Site::all()->keys()->all();
    }

    /**
     * Get the default locale.
     *
     * @return mixed
     */
    public function getDefaultLocale(): string
    {
        return Site::default()->handle();
    }

    /**
     * Get the locales that aren't the current (or specified) one.
     *
     * @param  string|null  $locale  The locale to treat as the current one
     * @return array
     */
    public function getOtherLocales($locale = null)
    {
        if (! $locale) {
            $locale = Site::current()->handle();
        }

        return array_values(array_diff($this->getLocales(), [$locale]));
    }

    /**
     * Get the site URL.
     *
     * @param  string|null  $locale  Optionally get the site url for a locale
     * @return mixed
     */
    public function getSiteUrl($locale = null): string
    {
        return $this->getSite($locale)->url();
    }
}
