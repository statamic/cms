<?php

namespace Statamic\Preferences;

use Closure;
use Statamic\API\Arr;
use Statamic\API\Str;

trait HasPreferences
{
    protected $preferences = [];

    /**
     * Get or set preferences.
     *
     * @param null|array $preferences
     * @return $this
     */
    public function preferences($preferences = null)
    {
        if (is_null($preferences)) {
            return $this->preferences;
        }

        $this->setPreferences($preferences);

        return $this;
    }

    /**
     * Set/merge array of preferences.
     *
     * @param array $preferences
     * @return $this
     */
    public function setPreferences($preferences)
    {
        $this->preferences = array_merge($this->preferences, Arr::wrap($preferences));

        return $this;
    }

    /**
     * Set preference (dot notation in key supported).
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setPreference($key, $value)
    {
        Arr::set($this->preferences, $key, $value);

        return $this;
    }

    /**
     * Remove preference (dot notation in key supported).
     *
     * @param string $key
     * @param null|mixed $value
     * @return $this
     */
    public function removePreference($key, $value = null)
    {
        if (is_null($value)) {
            Arr::pull($this->preferences, $key);
        } else {
            $this->removePreferenceValue($key, $value);
        }

        return $this;
    }

    /**
     * Remove an array value from a preference.
     *
     * @param string $key
     * @param mixed $value
     */
    public function removePreferenceValue($key, $value)
    {
        $values = collect($this->getPreference($key));

        $removableKey = $values->search($value);

        if ($removableKey !== false) {
            $this->setPreference($key, $values->forget($removableKey)->values()->all());
        }

        return $this;
    }

    /**
     * Get preference (dot notation in key supported).
     *
     * @param string $key
     * @return mixed
     */
    public function getPreference($key)
    {
        return Arr::get($this->preferences, $key);
    }

    /**
     * Check if preference exists (dot notation in key supported).
     *
     * @param string $key
     * @return bool
     */
    public function hasPreference($key)
    {
        return Arr::has($this->preferences, $key);
    }

    /**
     * Modify a preference using a callback.
     *
     * @param string $key
     * @param Closure $callback
     * @return $this
     */
    public function modifyPreference($key, Closure $callback)
    {
        $value = $this->getPreference($key);

        Arr::set($this->preferences, $key, $callback($value));

        return $this;
    }

    /**
     * Append array of preferences onto an array of preferences.
     *
     * @param string $key
     * @param array $array
     * @return $this
     */
    public function appendPreferences($key, $array)
    {
        foreach ($array as $value) {
            $this->appendPreference($key, $value);
        }

        return $this;
    }

    /**
     * Append a value onto an array of preferences (will convert to array if necessary).
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function appendPreference($key, $value)
    {
        return $this->modifyPreference($key, function ($original) use ($value) {
            $array = (array) $original;
            $array[] = $value;

            return $array;
        });
    }

    /**
     * Cleanup preference and it's parents to avoid leaving empty array/object data in yaml.
     *
     * @param string $key
     * @return $this
     */
    public function cleanupPreference($key)
    {
        $preference = $this->getPreference($key);

        if (is_int($preference) || is_bool($preference) || $preference) {
            return $this;
        }

        if ($this->hasPreference($key)) {
            $this->removePreference($key);
        }

        return Str::contains($key, '.')
            ? $this->cleanupPreference(preg_replace('/\.[^.]+$/', '', $key))
            : $this;
    }
}
