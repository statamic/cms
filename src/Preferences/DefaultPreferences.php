<?php

namespace Statamic\Preferences;

use Statamic\Facades\File;
use Statamic\Facades\YAML;

class DefaultPreferences
{
    use HasPreferencesInProperty;

    protected $path;

    /**
     * Instantiate default preferences instance.
     */
    public function __construct()
    {
        $this->path = resource_path('preferences.yaml');

        $this->preferences = YAML::file($this->path)->parse();
    }

    /**
     * Get all default preferences.
     *
     * @return array
     */
    public function all()
    {
        return $this->getPreferences();
    }

    /**
     * Save preferences to file.
     *
     * @param  array  $preferences
     * @return array
     */
    public function save()
    {
        File::put($this->path, YAML::dump($this->preferences));

        return true;
    }

    /**
     * Set all the preferences.
     *
     * @param  string|array  $key  Either an array to set all preferences at once, or a string key to set a single preference.
     * @param  mixed  $value  If passing a string key, the value to set.
     * @return $this
     */
    public function set($key, $value = null)
    {
        return func_num_args() === 2
            ? $this->setPreference($key, $value)
            : $this->setPreferences($key);
    }

    /**
     * Merge preferences.
     *
     * @param  array  $preferences
     * @return $this
     */
    public function merge($preferences)
    {
        return $this->mergePreferences($preferences);
    }
}
