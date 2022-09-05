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
    public static function all()
    {
        return (new static)->getPreferences();
    }

    /**
     * Save preferences to file.
     *
     * @param  array  $preferences
     * @return array
     */
    public static function save($preferences)
    {
        $instance = (new static);

        $instance->mergePreferences($preferences);

        File::put($instance->path, YAML::dump($instance->preferences));

        return true;
    }
}
