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
     * Magically call `setPreferences()` and `mergePreferences()` via `set()` and `merge()`, etc.
     *
     * @param  mixed  $name
     * @param  mixed  $arguments
     */
    public function __call($name, $arguments)
    {
        $method = $name.'Preferences';

        return $this->{$method}(...$arguments);
    }
}
